<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class BackupService
{
    /**
     * Tables that are regenerable/ephemeral — restoring them would just
     * invalidate every active session and clutter the DB with stale queue
     * rows, so they're skipped on both backup and restore.
     */
    private const EXCLUDED_TABLES = [
        'migrations', 'sessions', 'cache', 'cache_locks',
        'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens',
    ];

    public function tables(): array
    {
        return collect(DB::select("select tablename from pg_tables where schemaname = 'public'"))
            ->pluck('tablename')
            ->reject(fn ($t) => in_array($t, self::EXCLUDED_TABLES, true))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{table: string, rows: array}>
     */
    public function export(): array
    {
        $order = $this->insertOrder();

        $export = [];
        foreach ($order as $table) {
            $export[] = [
                'table' => $table,
                'rows' => DB::table($table)->get()->map(fn ($row) => (array) $row)->all(),
            ];
        }

        return $export;
    }

    /**
     * Wipe and reload every table in the backup, in a single transaction.
     * Tables are deleted in reverse dependency order (children first) and
     * reinserted in forward order (parents first) so foreign keys never
     * point at a row that doesn't exist yet.
     *
     * Rows are filtered down to columns that actually exist on the live
     * table before inserting — a backup taken from a schema with extra
     * (e.g. dev-only, migration-less) columns would otherwise fail the
     * whole restore with an "undefined column" error.
     *
     * @param  array<int, array{table: string, rows: array}>  $data
     * @param  ?callable(string $table, int $rowCount, array $droppedColumns): void  $onTable  called once per table, right before it's restored
     */
    public function restore(array $data, ?callable $onTable = null): void
    {
        $knownTables = array_flip($this->tables());
        $byTable = collect($data)->filter(fn ($entry) => isset($entry['table'], $entry['rows']) && isset($knownTables[$entry['table']]))
            ->keyBy('table');

        $order = $this->insertOrder();
        $tablesInBackup = array_values(array_intersect($order, $byTable->keys()->all()));

        DB::transaction(function () use ($tablesInBackup, $byTable, $onTable) {
            foreach (array_reverse($tablesInBackup) as $table) {
                DB::table($table)->delete();
            }

            foreach ($tablesInBackup as $table) {
                $validColumns = array_flip(Schema::getColumnListing($table));
                $rows = $byTable[$table]['rows'];
                $droppedColumns = [];

                $rows = array_map(function ($row) use ($validColumns, &$droppedColumns) {
                    $droppedColumns += array_diff_key($row, $validColumns);

                    return array_intersect_key($row, $validColumns);
                }, $rows);

                if ($onTable) {
                    $onTable($table, count($rows), array_keys($droppedColumns));
                }

                foreach (array_chunk($rows, 500) as $chunk) {
                    if (! empty($chunk)) {
                        DB::table($table)->insert($chunk);
                    }
                }

                $this->resetSequenceFor($table);
            }
        });
    }

    /**
     * Restored rows carry their original explicit "id" values, which leaves
     * the table's auto-increment sequence still at its pre-restore value —
     * the next normal insert would then collide with an already-restored id
     * and fail with a unique constraint violation. Point the sequence past
     * the highest id actually in the table now.
     */
    private function resetSequenceFor(string $table): void
    {
        if (! Schema::hasColumn($table, 'id')) {
            return;
        }

        $sequence = DB::selectOne('select pg_get_serial_sequence(?, ?) as name', [$table, 'id'])->name ?? null;

        if ($sequence === null) {
            return;
        }

        DB::statement("select setval(?, coalesce((select max(id) from \"{$table}\"), 1), (select max(id) from \"{$table}\") is not null)", [$sequence]);
    }

    /**
     * Locally-stored media (images, documents) that actually exist on disk.
     * Videos/audio live on Mux, not locally, so there's nothing to bundle
     * for those — they're already safely stored off-server.
     *
     * @return array<int, string> storage_path values, relative to the public disk
     */
    public function localMediaPaths(): array
    {
        return Media::query()
            ->where('provider', 'local')
            ->whereNotNull('storage_path')
            ->pluck('storage_path')
            ->filter(fn ($path) => Storage::disk('public')->exists($path))
            ->values()
            ->all();
    }

    /**
     * Build a zip containing the data export (data.json) plus every local
     * media file, and return the path to the temp file. Caller is
     * responsible for deleting it after sending.
     */
    public function buildZip(): string
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'eb-backup-').'.zip';

        $zip = new \ZipArchive();
        $zip->open($tmpPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $zip->addFromString('data.json', json_encode([
            'app' => 'EnglishBaro',
            'created_at' => now()->toIso8601String(),
            'tables' => $this->export(),
        ]));

        $disk = Storage::disk('public');
        foreach ($this->localMediaPaths() as $path) {
            $zip->addFile($disk->path($path), 'media/'.$path);
        }

        $zip->close();

        return $tmpPath;
    }

    /**
     * Copy every file under media/ in the given zip back onto the public
     * disk, overwriting whatever is there now.
     */
    public function restoreMediaFromZip(\ZipArchive $zip): int
    {
        $disk = Storage::disk('public');
        $count = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            if (! $name || ! str_starts_with($name, 'media/')) {
                continue;
            }

            $relative = substr($name, strlen('media/'));
            $contents = $zip->getFromIndex($i);

            if ($contents === false || $relative === '') {
                continue;
            }

            $disk->put($relative, $contents);
            $count++;
        }

        return $count;
    }

    /**
     * Topologically sorts tables so every table appears after all tables it
     * has a foreign key pointing to (safe insert order; the reverse is the
     * safe delete order).
     *
     * @return array<int, string>
     */
    private function insertOrder(): array
    {
        $tables = $this->tables();

        $fks = DB::select("
            select
                tc.table_name as child,
                ccu.table_name as parent
            from information_schema.table_constraints tc
            join information_schema.constraint_column_usage ccu on tc.constraint_name = ccu.constraint_name
            where tc.constraint_type = 'FOREIGN KEY' and tc.table_schema = 'public' and tc.table_name != ccu.table_name
        ");

        $dependsOn = array_fill_keys($tables, []);
        foreach ($fks as $fk) {
            if (isset($dependsOn[$fk->child]) && isset($dependsOn[$fk->parent])) {
                $dependsOn[$fk->child][] = $fk->parent;
            }
        }

        $ordered = [];
        $visited = [];
        $inProgress = [];

        $visit = function (string $table) use (&$visit, &$visited, &$inProgress, &$ordered, $dependsOn) {
            if (isset($visited[$table]) || isset($inProgress[$table])) {
                // Already placed, or a circular FK reference — stop here
                // rather than recurse forever; the cyclical pair will still
                // both end up in $ordered via their own top-level visit.
                return;
            }
            $inProgress[$table] = true;

            foreach ($dependsOn[$table] as $parent) {
                $visit($parent);
            }

            unset($inProgress[$table]);
            $visited[$table] = true;
            $ordered[] = $table;
        };

        foreach ($tables as $table) {
            $visit($table);
        }

        return $ordered;
    }
}
