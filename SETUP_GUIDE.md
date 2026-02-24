# ITGC System Setup Guide

## Prerequisites

- PHP >= 8.2
- Composer
- MySQL >= 8.0
- Node.js >= 18
- npm or yarn

## Step-by-Step Installation

### 1. Backend (Laravel) Setup

#### 1.1 Install Dependencies
```bash
cd backend
composer install
```

#### 1.2 Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Edit .env file and configure:
DB_DATABASE=itgc_system
DB_USERNAME=root
DB_PASSWORD=your_password
```

#### 1.3 Generate Keys
```bash
# Generate application key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret
```

#### 1.4 Database Setup
```bash
# Create database
mysql -u root -p
CREATE DATABASE itgc_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Run migrations
php artisan migrate

# Seed database with default data
php artisan migrate --seed
```

#### 1.5 Storage Link
```bash
# Create storage symbolic link
php artisan storage:link
```

#### 1.6 Start Development Server
```bash
php artisan serve
# Server will run on http://localhost:8000
```

### 2. Frontend (Vue.js) Setup

#### 2.1 Install Dependencies
```bash
cd frontend
npm install
```

#### 2.2 Environment Configuration (Optional)
Create `.env` file in frontend directory:
```
VITE_API_URL=http://localhost:8000/api
```

#### 2.3 Start Development Server
```bash
npm run dev
# Server will run on http://localhost:5173
```

## Default Login Credentials

After seeding, you can login with these accounts:

| Role | Email | Password | Description |
|------|-------|----------|-------------|
| Super Admin | admin@itgc.com | password | Full system access |
| IT Admin | itadmin@itgc.com | password | IT administration |
| Auditor | auditor@itgc.com | password | Read-only access |
| User | user@itgc.com | password | Basic user access |

## API Documentation

### Base URL
```
http://localhost:8000/api
```

### Authentication Endpoints

#### POST /login
```json
{
  "email": "admin@itgc.com",
  "password": "password"
}
```

Response:
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { ... },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

#### POST /logout
Headers: `Authorization: Bearer {token}`

### Change Request Endpoints

#### GET /change-requests
Get all change requests with pagination and filters.

Query params:
- `page`: Page number
- `per_page`: Items per page
- `search`: Search term
- `status`: Filter by status
- `risk_level`: Filter by risk level

#### POST /change-requests
Create new change request.

Body:
```json
{
  "title": "Database Migration",
  "system_id": 1,
  "change_type": "enhancement",
  "risk_level": "high",
  "description": "Migrate database to new server",
  "impact_analysis": "System downtime required",
  "rollback_plan": "Restore from backup",
  "planned_start": "2024-03-01 10:00:00",
  "planned_end": "2024-03-01 12:00:00"
}
```

#### GET /change-requests/{id}
Get single change request details.

#### PUT /change-requests/{id}
Update change request.

#### DELETE /change-requests/{id}
Delete change request.

#### POST /change-requests/{id}/submit
Submit change request for approval.

#### POST /change-requests/{id}/evidence
Upload evidence file (multipart/form-data).

## Permissions System

### Modules
- users
- roles
- permissions
- systems
- departments
- access-requests
- change-requests
- backup-logs
- it-assets
- audit-logs

### Actions
- view
- create
- update
- delete
- approve
- verify
- assign
- export

### Permission Format
`{action}-{module}`

Examples:
- `view-change-requests`
- `create-change-requests`
- `approve-change-requests`

## Role Permissions

### Super Admin
- All permissions

### IT Admin
- Full access to:
  - systems
  - access-requests
  - change-requests
  - backup-logs
  - it-assets

### Auditor
- Read-only access to all modules
- Export audit logs

### User
- View and create access requests
- View and create change requests
- View IT assets

## Security Features

✅ JWT token-based authentication
✅ Token refresh mechanism
✅ Password hashing (bcrypt)
✅ Role-based access control
✅ Permission-based authorization
✅ Audit logging
✅ CSRF protection
✅ SQL injection prevention (Eloquent ORM)
✅ XSS protection
✅ Rate limiting (can be configured)

## Production Deployment

### Backend

1. Set environment to production:
```bash
APP_ENV=production
APP_DEBUG=false
```

2. Optimize application:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

3. Set proper permissions:
```bash
chmod -R 755 storage bootstrap/cache
```

### Frontend

1. Build for production:
```bash
npm run build
```

2. Deploy `dist` folder to web server

## Troubleshooting

### JWT Secret Not Set
```bash
php artisan jwt:secret
```

### Storage Permission Denied
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### Database Connection Failed
Check your `.env` file database credentials.

### CORS Errors
Add CORS middleware in `app/Http/Kernel.php` or use `laravel-cors` package.

## Additional Features to Implement

- [ ] Email notifications for approvals
- [ ] Real-time notifications (Pusher/WebSocket)
- [ ] Advanced reporting
- [ ] Export to PDF/Excel
- [ ] Calendar view for scheduled changes
- [ ] Mobile responsive improvements
- [ ] Multi-language support
- [ ] Two-factor authentication
- [ ] Password reset functionality
- [ ] Activity timeline
- [ ] File preview
- [ ] Advanced search with filters

## Support

For issues or questions, please check:
- Laravel Documentation: https://laravel.com/docs
- Vue.js Documentation: https://vuejs.org/guide
- Bootstrap Documentation: https://getbootstrap.com/docs
