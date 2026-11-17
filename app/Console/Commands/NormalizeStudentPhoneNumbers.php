<?php

namespace App\Console\Commands;

use App\Models\StudentProfile;
use App\Support\PhoneNumberFormatter;
use Illuminate\Console\Command;

class NormalizeStudentPhoneNumbers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:normalize-student-phone-numbers {--dry-run : Show what would change without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'One-off backfill: prefix existing student phone numbers with their country\'s dial code';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        StudentProfile::query()
            ->whereNotNull('phone')
            ->whereNotNull('country')
            ->chunkById(200, function ($profiles) use ($dryRun) {
                foreach ($profiles as $profile) {
                    $normalized = PhoneNumberFormatter::normalize($profile->phone, $profile->country);

                    if ($normalized === $profile->phone) {
                        continue;
                    }

                    $this->line("#{$profile->id} ({$profile->country}): {$profile->phone} -> {$normalized}");

                    if (! $dryRun) {
                        $profile->update(['phone' => $normalized]);
                    }
                }
            });

        $this->info($dryRun ? 'Dry run complete — no changes saved.' : 'Done.');

        return self::SUCCESS;
    }
}
