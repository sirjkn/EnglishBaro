<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function download(BackupService $backup): BinaryFileResponse
    {
        $tmpPath = $backup->buildZip();
        $filename = 'englishbaro-backup-'.now()->format('Y-m-d_His').'.zip';

        AuditLogger::log('admin.backup.downloaded', null, [], ['filename' => $filename]);

        return response()->download($tmpPath, $filename, ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend(true);
    }

    /**
     * Reads the uploaded file and reports what it contains (tables, row
     * counts, when it was taken) without touching the database at all, so
     * the admin can see what they're about to overwrite before confirming.
     */
    public function preview(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'backup_file' => ['required', 'file', 'max:512000'],
        ]);

        $file = $validated['backup_file'];
        $filePath = $file->getRealPath();
        $isZip = str_ends_with(strtolower($file->getClientOriginalName()), '.zip');

        $mediaCount = 0;

        if ($isZip) {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) !== true) {
                return response()->json(['error' => 'That file is not a valid backup archive.'], 422);
            }

            $json = $zip->getFromName('data.json');
            if ($json === false) {
                return response()->json(['error' => 'That backup archive is missing its data.json.'], 422);
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                if (str_starts_with($zip->getNameIndex($i), 'media/')) {
                    $mediaCount++;
                }
            }
            $zip->close();
        } else {
            $json = file_get_contents($filePath);
        }

        $decoded = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded) || ! isset($decoded['tables']) || ! is_array($decoded['tables'])) {
            return response()->json(['error' => 'That backup file is invalid or corrupted.'], 422);
        }

        $tables = collect($decoded['tables'])
            ->map(fn ($entry) => ['table' => $entry['table'] ?? '?', 'rows' => count($entry['rows'] ?? [])])
            ->sortByDesc('rows')
            ->values();

        return response()->json([
            'created_at' => $decoded['created_at'] ?? null,
            'total_tables' => $tables->count(),
            'total_rows' => $tables->sum('rows'),
            'media_files' => $mediaCount,
            'tables' => $tables,
        ]);
    }

    /**
     * Restores the uploaded backup and returns a single JSON summary once
     * done. This used to stream progress line-by-line, but many shared
     * hosts fully buffer PHP output regardless of flush() calls, so the
     * browser received nothing until the whole restore finished — and
     * nothing at all if it ran past the host's execution time limit. A
     * plain synchronous request works reliably everywhere; the frontend
     * drives its progress bar from the already-fetched preview data instead.
     */
    public function restore(Request $request, BackupService $backup): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'backup_file' => ['required', 'file', 'max:512000'],
            'confirm' => ['required', 'in:RESTORE'],
        ]);

        $file = $validated['backup_file'];
        $filePath = $file->getRealPath();
        $originalName = $file->getClientOriginalName();
        $isZip = str_ends_with(strtolower($originalName), '.zip');

        $zip = null;
        $mediaRestored = 0;

        if ($isZip) {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) !== true) {
                return response()->json(['error' => 'That file is not a valid backup archive.'], 422);
            }

            $json = $zip->getFromName('data.json');
            if ($json === false) {
                return response()->json(['error' => 'That backup archive is missing its data.json.'], 422);
            }
        } else {
            $json = file_get_contents($filePath);
        }

        $decoded = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded) || ! isset($decoded['tables']) || ! is_array($decoded['tables'])) {
            return response()->json(['error' => 'That backup file is invalid or corrupted.'], 422);
        }

        $tablesRestored = [];
        $backup->restore($decoded['tables'], function (string $table, int $rowCount, array $dropped) use (&$tablesRestored) {
            $tablesRestored[] = ['table' => $table, 'rows' => $rowCount, 'dropped_columns' => $dropped];
        });

        if ($zip) {
            $mediaRestored = $backup->restoreMediaFromZip($zip);
            $zip->close();
        }

        AuditLogger::log('admin.backup.restored', null, [], [
            'filename' => $originalName,
            'backed_up_at' => $decoded['created_at'] ?? null,
            'media_files_restored' => $mediaRestored,
        ]);

        return response()->json([
            'tables' => $tablesRestored,
            'media_restored' => $mediaRestored,
        ]);
    }
}
