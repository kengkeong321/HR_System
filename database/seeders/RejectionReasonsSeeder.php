<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RejectionReasonsSeeder extends Seeder
{
    public function run(): void
    {
        $reasons = [
            ['reason_label' => 'Incorrect Allowances', 'reason_template' => 'Incorrect Allowance calculation detected for several staff members.'],
            ['reason_label' => 'Statutory Error', 'reason_template' => 'Missing or incorrect statutory contributions (EPF/SOCSO/EIS).'],
            ['reason_label' => 'Attendance Mismatch', 'reason_template' => 'Part-time hours do not match the attendance logs provided.'],
            ['reason_label' => 'Bank Detail Error', 'reason_template' => 'Detected incorrect or inactive bank account details.'],
            ['reason_label' => 'Duplicate Claims', 'reason_template' => 'Detected duplicate allowance claims for this month.'],
        ];

        foreach ($reasons as $reason) {
            DB::table('rejection_reasons')->updateOrInsert(
                ['reason_label' => $reason['reason_label']], 
                ['reason_template' => $reason['reason_template'], 'created_at' => now()]
            );
        }
    }
}