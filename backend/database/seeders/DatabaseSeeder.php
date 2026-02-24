<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Models\Department;
use App\Models\System;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Departments
        $itDept = Department::create([
            'code' => 'IT',
            'name' => 'Information Technology',
            'description' => 'IT Department',
            'is_active' => true,
        ]);

        $financeDept = Department::create([
            'code' => 'FIN',
            'name' => 'Finance',
            'description' => 'Finance Department',
            'is_active' => true,
        ]);

        // Create Roles
        $superAdmin = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full system access',
            'is_active' => true,
        ]);

        $itAdmin = Role::create([
            'name' => 'IT Admin',
            'slug' => 'it-admin',
            'description' => 'IT administration access',
            'is_active' => true,
        ]);

        $auditor = Role::create([
            'name' => 'Auditor',
            'slug' => 'auditor',
            'description' => 'Read-only audit access',
            'is_active' => true,
        ]);

        $user = Role::create([
            'name' => 'User',
            'slug' => 'user',
            'description' => 'Basic user access',
            'is_active' => true,
        ]);

        // Create Permissions
        $modules = [
            'users' => ['view', 'create', 'update', 'delete'],
            'roles' => ['view', 'create', 'update', 'delete'],
            'permissions' => ['view', 'create', 'update', 'delete'],
            'systems' => ['view', 'create', 'update', 'delete'],
            'departments' => ['view', 'create', 'update', 'delete'],
            'access-requests' => ['view', 'create', 'update', 'delete', 'approve'],
            'change-requests' => ['view', 'create', 'update', 'delete', 'approve'],
            'backup-logs' => ['view', 'create', 'update', 'verify'],
            'it-assets' => ['view', 'create', 'update', 'delete', 'assign'],
            'audit-logs' => ['view', 'export'],
        ];

        $permissions = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permission = Permission::create([
                    'name' => ucfirst($action) . ' ' . ucfirst(str_replace('-', ' ', $module)),
                    'slug' => $action . '-' . $module,
                    'module' => $module,
                    'description' => "Can $action $module",
                    'is_active' => true,
                ]);
                $permissions[$module][$action] = $permission;
            }
        }

        // Assign all permissions to Super Admin
        $superAdmin->permissions()->attach(Permission::all()->pluck('id'));

        // Assign specific permissions to IT Admin
        $itAdminPermissions = Permission::whereIn('module', [
            'systems', 'access-requests', 'change-requests', 'backup-logs', 'it-assets'
        ])->pluck('id');
        $itAdmin->permissions()->attach($itAdminPermissions);

        // Assign read-only permissions to Auditor
        $auditorPermissions = Permission::where('slug', 'like', 'view-%')
            ->orWhere('slug', 'like', 'export-%')
            ->pluck('id');
        $auditor->permissions()->attach($auditorPermissions);

        // Assign basic permissions to User
        $userPermissions = Permission::whereIn('slug', [
            'view-access-requests',
            'create-access-requests',
            'view-change-requests',
            'create-change-requests',
            'view-it-assets',
        ])->pluck('id');
        $user->permissions()->attach($userPermissions);

        // Create Users
        $superAdminUser = User::create([
            'employee_id' => 'ADM001',
            'name' => 'Super Admin',
            'email' => 'admin@itgc.com',
            'password' => Hash::make('password'),
            'department_id' => $itDept->id,
            'position' => 'System Administrator',
            'phone' => '08123456789',
            'is_active' => true,
        ]);
        $superAdminUser->roles()->attach($superAdmin->id);

        $itAdminUser = User::create([
            'employee_id' => 'IT001',
            'name' => 'IT Admin',
            'email' => 'itadmin@itgc.com',
            'password' => Hash::make('password'),
            'department_id' => $itDept->id,
            'position' => 'IT Manager',
            'phone' => '08123456780',
            'is_active' => true,
        ]);
        $itAdminUser->roles()->attach($itAdmin->id);

        $auditorUser = User::create([
            'employee_id' => 'AUD001',
            'name' => 'Auditor',
            'email' => 'auditor@itgc.com',
            'password' => Hash::make('password'),
            'department_id' => $financeDept->id,
            'position' => 'Internal Auditor',
            'phone' => '08123456781',
            'is_active' => true,
        ]);
        $auditorUser->roles()->attach($auditor->id);

        $regularUser = User::create([
            'employee_id' => 'USR001',
            'name' => 'Regular User',
            'email' => 'user@itgc.com',
            'password' => Hash::make('password'),
            'department_id' => $financeDept->id,
            'position' => 'Staff',
            'phone' => '08123456782',
            'is_active' => true,
        ]);
        $regularUser->roles()->attach($user->id);

        // Create Systems
        System::create([
            'code' => 'ERP-001',
            'name' => 'Enterprise Resource Planning',
            'description' => 'Main ERP System',
            'category' => 'ERP',
            'version' => '5.0',
            'vendor' => 'SAP',
            'owner_user_id' => $itAdminUser->id,
            'is_active' => true,
            'created_by' => $superAdminUser->id,
        ]);

        System::create([
            'code' => 'CRM-001',
            'name' => 'Customer Relationship Management',
            'description' => 'CRM System',
            'category' => 'CRM',
            'version' => '3.2',
            'vendor' => 'Salesforce',
            'owner_user_id' => $itAdminUser->id,
            'is_active' => true,
            'created_by' => $superAdminUser->id,
        ]);

        System::create([
            'code' => 'HRM-001',
            'name' => 'Human Resource Management',
            'description' => 'HR Management System',
            'category' => 'HRM',
            'version' => '2.5',
            'vendor' => 'Workday',
            'owner_user_id' => $itAdminUser->id,
            'is_active' => true,
            'created_by' => $superAdminUser->id,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('Super Admin - admin@itgc.com / password');
        $this->command->info('IT Admin - itadmin@itgc.com / password');
        $this->command->info('Auditor - auditor@itgc.com / password');
        $this->command->info('User - user@itgc.com / password');
    }
}
