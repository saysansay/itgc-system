# ITGC System - Project Structure

## Backend (Laravel 11)

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       ├── DashboardController.php
│   │   │       ├── ChangeRequestController.php
│   │   │       ├── AccessRequestController.php
│   │   │       ├── BackupLogController.php
│   │   │       ├── ItAssetController.php
│   │   │       ├── AuditLogController.php
│   │   │       ├── UserController.php
│   │   │       ├── RoleController.php
│   │   │       ├── PermissionController.php
│   │   │       ├── SystemController.php
│   │   │       └── DepartmentController.php
│   │   ├── Middleware/
│   │   │   └── CheckPermission.php
│   │   ├── Requests/
│   │   │   └── ChangeRequestRequest.php
│   │   └── Resources/
│   │       └── ChangeRequestResource.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── Permission.php
│   │   ├── Department.php
│   │   ├── System.php
│   │   ├── AccessRequest.php
│   │   ├── ChangeRequest.php
│   │   ├── ChangeEvidence.php
│   │   ├── Approval.php
│   │   ├── BackupLog.php
│   │   ├── RestoreTestLog.php
│   │   ├── ItAsset.php
│   │   ├── AssetAssignment.php
│   │   └── AuditLog.php
│   ├── Repositories/
│   │   ├── BaseRepository.php
│   │   └── ChangeRequestRepository.php
│   └── Services/
│       └── ChangeRequestService.php
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_roles_table.php
│   │   ├── 2024_01_01_000002_create_permissions_table.php
│   │   ├── 2024_01_01_000003_create_departments_table.php
│   │   ├── 2024_01_01_000004_create_users_table.php
│   │   ├── 2024_01_01_000005_create_role_user_table.php
│   │   ├── 2024_01_01_000006_create_permission_role_table.php
│   │   ├── 2024_01_01_000007_create_systems_table.php
│   │   ├── 2024_01_01_000008_create_access_requests_table.php
│   │   ├── 2024_01_01_000009_create_change_requests_table.php
│   │   ├── 2024_01_01_000010_create_change_evidences_table.php
│   │   ├── 2024_01_01_000011_create_approvals_table.php
│   │   ├── 2024_01_01_000012_create_backup_logs_table.php
│   │   ├── 2024_01_01_000013_create_restore_test_logs_table.php
│   │   ├── 2024_01_01_000014_create_it_assets_table.php
│   │   ├── 2024_01_01_000015_create_asset_assignments_table.php
│   │   └── 2024_01_01_000016_create_audit_logs_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── routes/
│   └── api.php
├── .env.example
└── composer.json

## Frontend (Vue.js 3)

```
frontend/
├── src/
│   ├── assets/
│   │   └── main.css
│   ├── layouts/
│   │   ├── AuthLayout.vue
│   │   └── MainLayout.vue
│   ├── views/
│   │   ├── auth/
│   │   │   └── Login.vue
│   │   ├── change-requests/
│   │   │   ├── List.vue
│   │   │   ├── Form.vue
│   │   │   └── Detail.vue
│   │   └── Dashboard.vue
│   ├── services/
│   │   ├── api.js
│   │   ├── authService.js
│   │   └── changeRequestService.js
│   ├── router/
│   │   └── index.js
│   ├── App.vue
│   └── main.js
├── index.html
├── package.json
└── vite.config.js
```

## Installation Instructions

### Backend Setup

1. Install dependencies:
```bash
cd backend
composer install
```

2. Configure environment:
```bash
cp .env.example .env
# Edit .env file with your database credentials
```

3. Generate application key and JWT secret:
```bash
php artisan key:generate
php artisan jwt:secret
```

4. Run migrations and seeders:
```bash
php artisan migrate --seed
```

5. Start the server:
```bash
php artisan serve
```

### Frontend Setup

1. Install dependencies:
```bash
cd frontend
npm install
```

2. Start development server:
```bash
npm run dev
```

## Default Login Credentials

- **Super Admin**: admin@itgc.com / password
- **IT Admin**: itadmin@itgc.com / password
- **Auditor**: auditor@itgc.com / password
- **User**: user@itgc.com / password

## Features Implemented

✅ JWT Authentication
✅ Role-Based Access Control (RBAC)
✅ Dashboard with statistics and charts
✅ Change Request Management (CRUD)
✅ Evidence upload
✅ Approval workflow
✅ Audit logging
✅ Complete database schema with relationships
✅ Service and Repository layers
✅ Form validation
✅ API resources
✅ Responsive layout (White sidebar, Red topbar)
✅ Bootstrap 5 UI

## Additional Controllers to Implement

Create similar controllers for:
- UserController
- RoleController
- PermissionController
- SystemController
- DepartmentController
- AccessRequestController
- BackupLogController
- ItAssetController
- AuditLogController
- ApprovalController

Follow the same pattern as ChangeRequestController with Service and Repository layers.
