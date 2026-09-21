<?php

namespace App\Services;

use App\Models\StudentProfile;
use Illuminate\Support\Facades\DB;

class StudentIdGenerator
{
    public function generate(): string
    {
        return DB::transaction(function () {
            $last = StudentProfile::query()
                ->lockForUpdate()
                ->orderByDesc('id')
                ->value('student_id');

            $nextNumber = 1;

            if ($last && preg_match('/(\d+)$/', $last, $matches)) {
                $nextNumber = ((int) $matches[1]) + 1;
            }

            return sprintf('EB-%05d', $nextNumber);
        });
    }
}
