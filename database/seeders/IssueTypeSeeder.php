<?php

namespace Database\Seeders;

use App\Models\IssueType;
use Illuminate\Database\Seeder;

class IssueTypeSeeder extends Seeder
{
    public function run(): void
    {
        $issueTypes = [

            // ── Recruitment & Onboarding ──────────────────────────
            [
                'name' => 'Job Application Inquiry',
                'turnaround_time' => 'Within 2 business days',
                'sort_order' => 10,
            ],
            [
                'name' => 'Interview Scheduling',
                'turnaround_time' => 'Within 1 business day',
                'sort_order' => 11,
            ],
            [
                'name' => 'New Hire Onboarding',
                'turnaround_time' => '1–3 business days',
                'sort_order' => 12,
            ],
            [
                'name' => 'Employment Verification',
                'turnaround_time' => 'Within 1 business day',
                'sort_order' => 13,
            ],
            [
                'name' => 'Background Check Concern',
                'turnaround_time' => '1–2 business days',
                'sort_order' => 14,
            ],

            // ── Payroll & Compensation ────────────────────────────
            [
                'name' => 'Payroll / Salary Inquiry',
                'turnaround_time' => 'Within 1 business day',
                'sort_order' => 20,
            ],
            [
                'name' => 'Payslip Request',
                'turnaround_time' => 'Within 4 hours',
                'sort_order' => 21,
            ],
            [
                'name' => 'Overtime / Allowance Concern',
                'turnaround_time' => '1–2 business days',
                'sort_order' => 22,
            ],
            [
                'name' => 'Tax / 13th-Month Pay Inquiry',
                'turnaround_time' => '1–2 business days',
                'sort_order' => 23,
            ],
            [
                'name' => 'Final Pay / Back Pay Inquiry',
                'turnaround_time' => '3–5 business days',
                'sort_order' => 24,
            ],

            // ── Leave & Attendance ────────────────────────────────
            [
                'name' => 'Leave Application',
                'turnaround_time' => 'Within 1 business day',
                'sort_order' => 30,
            ],
            [
                'name' => 'Leave Balance Inquiry',
                'turnaround_time' => 'Within 4 hours',
                'sort_order' => 31,
            ],
            [
                'name' => 'Attendance / Timekeeping Issue',
                'turnaround_time' => 'Within 1 business day',
                'sort_order' => 32,
            ],
            [
                'name' => 'Schedule / Shift Adjustment',
                'turnaround_time' => '1–2 business days',
                'sort_order' => 33,
            ],

            // ── Benefits ──────────────────────────────────────────
            [
                'name' => 'HMO / Medical Benefit Inquiry',
                'turnaround_time' => '1–2 business days',
                'sort_order' => 40,
            ],
            [
                'name' => 'Government Benefits (SSS / PhilHealth / Pag-IBIG)',
                'turnaround_time' => '1–3 business days',
                'sort_order' => 41,
            ],
            [
                'name' => 'Retirement / Pension Inquiry',
                'turnaround_time' => '3–5 business days',
                'sort_order' => 42,
            ],
            [
                'name' => 'Insurance / Claim Assistance',
                'turnaround_time' => '1–3 business days',
                'sort_order' => 43,
            ],

            // ── Employee Relations ────────────────────────────────
            [
                'name' => 'Workplace Grievance / Complaint',
                'turnaround_time' => 'Within 1 business day',
                'sort_order' => 50,
            ],
            [
                'name' => 'Conflict / Dispute Resolution',
                'turnaround_time' => '1–3 business days',
                'sort_order' => 51,
            ],
            [
                'name' => 'Disciplinary Concern',
                'turnaround_time' => 'Within 1 business day',
                'sort_order' => 52,
            ],
            [
                'name' => 'Harassment / Misconduct Report',
                'turnaround_time' => 'Within 4 hours (confidential)',
                'sort_order' => 53,
            ],

            // ── Records & Documents ───────────────────────────────
            [
                'name' => 'Certificate of Employment Request',
                'turnaround_time' => '1–2 business days',
                'sort_order' => 60,
            ],
            [
                'name' => '201 File / Record Update',
                'turnaround_time' => 'Within 1 business day',
                'sort_order' => 61,
            ],
            [
                'name' => 'Contract / Document Reissuance',
                'turnaround_time' => '1–2 business days',
                'sort_order' => 62,
            ],

            // ── Training & Development ────────────────────────────
            [
                'name' => 'Training Program Inquiry',
                'turnaround_time' => '1–2 business days',
                'sort_order' => 70,
            ],
            [
                'name' => 'Performance Review Question',
                'turnaround_time' => '1–2 business days',
                'sort_order' => 71,
            ],
            [
                'name' => 'Career Development Discussion',
                'turnaround_time' => '2–5 business days',
                'sort_order' => 72,
            ],

            // ── Movement & Separation ─────────────────────────────
            [
                'name' => 'Internal Transfer / Promotion Inquiry',
                'turnaround_time' => '2–5 business days',
                'sort_order' => 80,
            ],
            [
                'name' => 'Resignation / Clearance Processing',
                'turnaround_time' => '3–5 business days',
                'sort_order' => 81,
            ],

            // ── Other ─────────────────────────────────────────────
            [
                'name' => 'HR Consultation / General Inquiry',
                'turnaround_time' => 'Within 1 business day',
                'sort_order' => 90,
            ],
            [
                'name' => 'Other / Not Listed',
                'turnaround_time' => 'Within 1 business day',
                'sort_order' => 99,
            ],
        ];

        foreach ($issueTypes as $data) {
            IssueType::firstOrCreate(
                ['name' => $data['name']],
                array_merge($data, ['is_active' => true])
            );
        }

        $this->command->info('Seeded '.count($issueTypes).' issue types.');
    }
}
