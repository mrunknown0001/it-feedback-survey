<?php

namespace Database\Seeders;

use App\Models\IssueType;
use Illuminate\Database\Seeder;

class IssueTypeSeeder extends Seeder
{
    public function run(): void
    {
        $issueTypes = [

            // ── Hardware ──────────────────────────────────────────
            [
                'name'           => 'Computer / Laptop Not Turning On',
                'turnaround_time' => 'Within 4 hours',
                'sort_order'     => 10,
            ],
            [
                'name'           => 'Computer Running Slowly',
                'turnaround_time' => 'Within 1 business day',
                'sort_order'     => 11,
            ],
            [
                'name'           => 'Monitor / Display Issue',
                'turnaround_time' => 'Within 4 hours',
                'sort_order'     => 12,
            ],
            [
                'name'           => 'Keyboard or Mouse Not Working',
                'turnaround_time' => 'Within 2 hours',
                'sort_order'     => 13,
            ],
            [
                'name'           => 'Peripheral Device Setup (Printer, Scanner, etc.)',
                'turnaround_time' => 'Within 1 business day',
                'sort_order'     => 14,
            ],
            [
                'name'           => 'Hardware Replacement Request',
                'turnaround_time' => '2–5 business days',
                'sort_order'     => 15,
            ],

            // ── Network & Connectivity ────────────────────────────
            [
                'name'           => 'No Internet Connection',
                'turnaround_time' => 'Within 2 hours',
                'sort_order'     => 20,
            ],
            [
                'name'           => 'Slow Internet / Network Connection',
                'turnaround_time' => 'Within 4 hours',
                'sort_order'     => 21,
            ],
            [
                'name'           => 'Cannot Connect to Wi-Fi',
                'turnaround_time' => 'Within 2 hours',
                'sort_order'     => 22,
            ],
            [
                'name'           => 'VPN Access Issue',
                'turnaround_time' => 'Within 4 hours',
                'sort_order'     => 23,
            ],
            [
                'name'           => 'Network Drive / Shared Folder Not Accessible',
                'turnaround_time' => 'Within 4 hours',
                'sort_order'     => 24,
            ],

            // ── Account & Access ──────────────────────────────────
            [
                'name'           => 'Password Reset / Forgot Password',
                'turnaround_time' => 'Within 1 hour',
                'sort_order'     => 30,
            ],
            [
                'name'           => 'Account Locked Out',
                'turnaround_time' => 'Within 1 hour',
                'sort_order'     => 31,
            ],
            [
                'name'           => 'New User Account Creation',
                'turnaround_time' => '1–2 business days',
                'sort_order'     => 32,
            ],
            [
                'name'           => 'Access / Permission Request',
                'turnaround_time' => '1–2 business days',
                'sort_order'     => 33,
            ],
            [
                'name'           => 'Multi-Factor Authentication (MFA) Issue',
                'turnaround_time' => 'Within 2 hours',
                'sort_order'     => 34,
            ],

            // ── Software & Applications ───────────────────────────
            [
                'name'           => 'Software Installation Request',
                'turnaround_time' => '1–2 business days',
                'sort_order'     => 40,
            ],
            [
                'name'           => 'Software Not Opening / Crashing',
                'turnaround_time' => 'Within 4 hours',
                'sort_order'     => 41,
            ],
            [
                'name'           => 'Software Update / Upgrade',
                'turnaround_time' => '1–2 business days',
                'sort_order'     => 42,
            ],
            [
                'name'           => 'Microsoft Office / 365 Issue',
                'turnaround_time' => 'Within 4 hours',
                'sort_order'     => 43,
            ],
            [
                'name'           => 'Operating System Error / Blue Screen',
                'turnaround_time' => 'Within 4 hours',
                'sort_order'     => 44,
            ],

            // ── Email & Communication ─────────────────────────────
            [
                'name'           => 'Cannot Send or Receive Emails',
                'turnaround_time' => 'Within 2 hours',
                'sort_order'     => 50,
            ],
            [
                'name'           => 'Email Setup on New Device',
                'turnaround_time' => 'Within 4 hours',
                'sort_order'     => 51,
            ],
            [
                'name'           => 'Teams / Video Conferencing Issue',
                'turnaround_time' => 'Within 2 hours',
                'sort_order'     => 52,
            ],
            [
                'name'           => 'Spam or Phishing Email Report',
                'turnaround_time' => 'Within 4 hours',
                'sort_order'     => 53,
            ],

            // ── Printing ──────────────────────────────────────────
            [
                'name'           => 'Printer Not Working',
                'turnaround_time' => 'Within 2 hours',
                'sort_order'     => 60,
            ],
            [
                'name'           => 'Cannot Find / Connect to Printer',
                'turnaround_time' => 'Within 2 hours',
                'sort_order'     => 61,
            ],
            [
                'name'           => 'Print Quality Issue',
                'turnaround_time' => 'Within 1 business day',
                'sort_order'     => 62,
            ],

            // ── Security ──────────────────────────────────────────
            [
                'name'           => 'Virus / Malware Suspected',
                'turnaround_time' => 'Within 1 hour',
                'sort_order'     => 70,
            ],
            [
                'name'           => 'Unauthorized Access / Security Concern',
                'turnaround_time' => 'Within 1 hour',
                'sort_order'     => 71,
            ],
            [
                'name'           => 'Data Loss / Accidental Deletion',
                'turnaround_time' => 'Within 4 hours',
                'sort_order'     => 72,
            ],

            // ── Other ─────────────────────────────────────────────
            [
                'name'           => 'New Equipment / Device Setup',
                'turnaround_time' => '1–3 business days',
                'sort_order'     => 80,
            ],
            [
                'name'           => 'IT Consultation / General Inquiry',
                'turnaround_time' => 'Within 1 business day',
                'sort_order'     => 81,
            ],
            [
                'name'           => 'Other / Not Listed',
                'turnaround_time' => 'Within 1 business day',
                'sort_order'     => 99,
            ],
        ];

        foreach ($issueTypes as $data) {
            IssueType::firstOrCreate(
                ['name' => $data['name']],
                array_merge($data, ['is_active' => true])
            );
        }

        $this->command->info('Seeded ' . count($issueTypes) . ' issue types.');
    }
}
