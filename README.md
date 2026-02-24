# ITGC (IT General Control) System

## Tech Stack

### Backend
- Laravel 11
- MySQL
- JWT Authentication (tymon/jwt-auth)
- REST API
- Role-Based Access Control (RBAC)
- Server-side DataTables

### Frontend
- Vue.js 3 (Composition API)
- Vue Router
- Bootstrap 5
- Axios
- Custom Layout (White Sidebar, Red Topbar #e60000)

## Installation

### Backend Setup
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate --seed
php artisan serve
```

### Frontend Setup
```bash
cd frontend
npm install
npm run dev
```

## Features

1. Authentication (JWT)
2. Role-Based Access Control
3. User Access Management
4. Change Management
5. Backup & Recovery Control
6. IT Asset Control
7. Audit & Monitoring
8. Dashboard with Analytics

## Default Roles

- Super Admin
- IT Admin
- Auditor
- User

## API Documentation

Base URL: `http://localhost:8000/api`

Authentication: Bearer Token (JWT)
