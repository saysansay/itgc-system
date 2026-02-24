<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminAccessRequest;
use App\Models\User;
use App\Models\Department;

class AdminAccessRequestSeeder extends Seeder
{
    public function run(): void
    {
        $requestor = User::first();
        $approver = User::where('email', 'admin@itgc.com')->first() ?? $requestor;
        $department = Department::first();

        if (!$requestor || !$department) {
            $this->command->warn('Skipping AdminAccessRequestSeeder: users or departments not found.');
            return;
        }

        $seedData = [
            [
                'request_type' => 'temporary',
                'duration_value' => 4,
                'duration_unit' => 'hour',
                'method' => 'vpn',
                'hostname' => 'SRV-APP-01',
                'user_administrator' => 'admin.app',
                'purpose' => 'Temporary admin access for patching.',
                'requested_at' => now()->subDays(2),
                'partner' => 'Vendor A',
                'notes' => 'Apply security updates only.',
                'status' => 'pending',
            ],
            [
                'request_type' => 'maintenance',
                'duration_value' => 1,
                'duration_unit' => 'day',
                'method' => 'rdp',
                'hostname' => 'SRV-DB-02',
                'user_administrator' => 'db.admin',
                'purpose' => 'Database maintenance window.',
                'requested_at' => now()->subDays(5),
                'partner' => 'Vendor B',
                'notes' => 'Include backup verification.',
                'status' => 'approved',
                'approved_by' => $approver?->id,
                'approved_at' => now()->subDays(4),
            ],
            [
                'request_type' => 'emergency',
                'duration_value' => 2,
                'duration_unit' => 'hour',
                'method' => 'server_console',
                'hostname' => 'SRV-SEC-03',
                'user_administrator' => 'sec.admin',
                'purpose' => 'Emergency incident response.',
                'requested_at' => now()->subDays(1),
                'partner' => null,
                'notes' => 'Urgent response required.',
                'status' => 'rejected',
                'approved_by' => $approver?->id,
                'approved_at' => now()->subDays(1),
            ],
        ];

        foreach ($seedData as $data) {
            AdminAccessRequest::create(array_merge($data, [
                'request_number' => AdminAccessRequest::generateRequestNumber(),
                'requestor_id' => $requestor->id,
                'department_id' => $department->id,
                'created_by' => $requestor->id,
            ]));
        }
    }
}
