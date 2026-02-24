import { createRouter, createWebHistory } from 'vue-router'
import authService from '@/services/authService'

// Layouts
import AuthLayout from '@/layouts/AuthLayout.vue'
import MainLayout from '@/layouts/MainLayout.vue'

// Auth Pages
import Login from '@/views/auth/Login.vue'
import ChangePassword from '@/views/auth/ChangePassword.vue'
import ForgotPassword from '@/views/auth/ForgotPassword.vue'
import ResetPassword from '@/views/auth/ResetPassword.vue'
import Register from '@/views/auth/Register.vue'

// Main Pages
import Dashboard from '@/views/Dashboard.vue'
import ChangeRequestList from '@/views/change-requests/List.vue'
import ChangeRequestForm from '@/views/change-requests/Form.vue'
import ChangeRequestDetail from '@/views/change-requests/Detail.vue'
import UserList from '@/views/users/List.vue'
import UserForm from '@/views/users/Form.vue'
import RoleList from '@/views/roles/List.vue'
import RoleForm from '@/views/roles/Form.vue'
import SystemList from '@/views/systems/List.vue'
import SystemForm from '@/views/systems/Form.vue'
import DepartmentList from '@/views/departments/List.vue'
import DepartmentForm from '@/views/departments/Form.vue'
import AccessRequestList from '@/views/access-requests/List.vue'
import AccessRequestForm from '@/views/access-requests/Form.vue'
import AccessRequestDetail from '@/views/access-requests/Detail.vue'
import BackupLogList from '@/views/backup-logs/List.vue'
import BackupLogForm from '@/views/backup-logs/Form.vue'
import BackupLogDetail from '@/views/backup-logs/Detail.vue'
import ItAssetList from '@/views/it-assets/List.vue'
import ItAssetForm from '@/views/it-assets/Form.vue'
import ItAssetDetail from '@/views/it-assets/Detail.vue'
import UsbLoanList from '@/views/usb-loans/List.vue'
import UsbLoanForm from '@/views/usb-loans/Form.vue'
import UsbLoanDetail from '@/views/usb-loans/Detail.vue'
import AdminAccessRequestList from '@/views/admin-access-requests/List.vue'
import AdminAccessRequestForm from '@/views/admin-access-requests/Form.vue'
import AdminAccessRequestDetail from '@/views/admin-access-requests/Detail.vue'
import GeneralTroubleList from '@/views/general-troubles/List.vue'
import GeneralTroubleForm from '@/views/general-troubles/Form.vue'
import GeneralTroubleDetail from '@/views/general-troubles/Detail.vue'
import SecurityAccessRequestList from '@/views/security-access-requests/List.vue'
import SecurityAccessRequestForm from '@/views/security-access-requests/Form.vue'
import SecurityAccessRequestDetail from '@/views/security-access-requests/Detail.vue'

const routes = [
  {
    path: '/auth',
    component: AuthLayout,
    children: [
      {
        path: 'login',
        name: 'Login',
        component: Login,
        meta: { guest: true }
      },
      {
        path: 'forgot-password',
        name: 'ForgotPassword',
        component: ForgotPassword,
        meta: { guest: true }
      },
      {
        path: 'reset-password',
        name: 'ResetPassword',
        component: ResetPassword,
        meta: { guest: true }
      },
      {
        path: 'register',
        name: 'Register',
        component: Register,
        meta: { guest: true }
      }
    ]
  },
  {
    path: '/',
    component: MainLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/dashboard'
      },
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: Dashboard
      },
      {
        path: 'change-requests',
        name: 'ChangeRequests',
        component: ChangeRequestList
      },
      {
        path: 'change-requests/create',
        name: 'CreateChangeRequest',
        component: ChangeRequestForm
      },
      {
        path: 'change-requests/:id',
        name: 'ChangeRequestDetail',
        component: ChangeRequestDetail
      },
      {
        path: 'change-requests/:id/edit',
        name: 'EditChangeRequest',
        component: ChangeRequestForm
      },
      {
        path: 'users',
        name: 'Users',
        component: UserList
      },
      {
        path: 'users/create',
        name: 'CreateUser',
        component: UserForm
      },
      {
        path: 'users/:id/edit',
        name: 'EditUser',
        component: UserForm
      },
      {
        path: 'roles',
        name: 'Roles',
        component: RoleList
      },
      {
        path: 'roles/create',
        name: 'CreateRole',
        component: RoleForm
      },
      {
        path: 'roles/:id/edit',
        name: 'EditRole',
        component: RoleForm
      },
      {
        path: 'systems',
        name: 'Systems',
        component: SystemList
      },
      {
        path: 'systems/create',
        name: 'CreateSystem',
        component: SystemForm
      },
      {
        path: 'systems/:id/edit',
        name: 'EditSystem',
        component: SystemForm
      },
      {
        path: 'departments',
        name: 'Departments',
        component: DepartmentList
      },
      {
        path: 'departments/create',
        name: 'CreateDepartment',
        component: DepartmentForm
      },
      {
        path: 'departments/:id/edit',
        name: 'EditDepartment',
        component: DepartmentForm
      },
      {
        path: 'access-requests',
        name: 'AccessRequests',
        component: AccessRequestList
      },
      {
        path: 'access-requests/create',
        name: 'CreateAccessRequest',
        component: AccessRequestForm
      },
      {
        path: 'access-requests/:id',
        name: 'AccessRequestDetail',
        component: AccessRequestDetail
      },
      {
        path: 'access-requests/:id/edit',
        name: 'EditAccessRequest',
        component: AccessRequestForm
      },
      {
        path: 'backup-logs',
        name: 'BackupLogs',
        component: BackupLogList
      },
      {
        path: 'backup-logs/create',
        name: 'CreateBackupLog',
        component: BackupLogForm
      },
      {
        path: 'backup-logs/:id',
        name: 'BackupLogDetail',
        component: BackupLogDetail
      },
      {
        path: 'backup-logs/:id/edit',
        name: 'EditBackupLog',
        component: BackupLogForm
      },
      {
        path: 'it-assets',
        name: 'ItAssets',
        component: ItAssetList
      },
      {
        path: 'it-assets/create',
        name: 'CreateItAsset',
        component: ItAssetForm
      },
      {
        path: 'it-assets/:id',
        name: 'ItAssetDetail',
        component: ItAssetDetail
      },
      {
        path: 'it-assets/:id/edit',
        name: 'EditItAsset',
        component: ItAssetForm
      },
      {
        path: 'usb-loans',
        name: 'UsbLoans',
        component: UsbLoanList
      },
      {
        path: 'usb-loans/create',
        name: 'CreateUsbLoan',
        component: UsbLoanForm
      },
      {
        path: 'usb-loans/:id',
        name: 'UsbLoanDetail',
        component: UsbLoanDetail
      },
      {
        path: 'usb-loans/:id/edit',
        name: 'EditUsbLoan',
        component: UsbLoanForm
      },
      {
        path: 'admin-access-requests',
        name: 'AdminAccessRequests',
        component: AdminAccessRequestList
      },
      {
        path: 'admin-access-requests/create',
        name: 'CreateAdminAccessRequest',
        component: AdminAccessRequestForm
      },
      {
        path: 'admin-access-requests/:id',
        name: 'AdminAccessRequestDetail',
        component: AdminAccessRequestDetail
      },
      {
        path: 'admin-access-requests/:id/edit',
        name: 'EditAdminAccessRequest',
        component: AdminAccessRequestForm
      },
      {
        path: 'general-troubles',
        name: 'GeneralTroubles',
        component: GeneralTroubleList
      },
      {
        path: 'general-troubles/create',
        name: 'CreateGeneralTrouble',
        component: GeneralTroubleForm
      },
      {
        path: 'general-troubles/:id',
        name: 'GeneralTroubleDetail',
        component: GeneralTroubleDetail
      },
      {
        path: 'general-troubles/:id/edit',
        name: 'EditGeneralTrouble',
        component: GeneralTroubleForm
      },
      {
        path: 'security-access-requests',
        name: 'SecurityAccessRequests',
        component: SecurityAccessRequestList
      },
      {
        path: 'security-access-requests/create',
        name: 'CreateSecurityAccessRequest',
        component: SecurityAccessRequestForm
      },
      {
        path: 'security-access-requests/:id',
        name: 'SecurityAccessRequestDetail',
        component: SecurityAccessRequestDetail
      },
      {
        path: 'security-access-requests/:id/edit',
        name: 'EditSecurityAccessRequest',
        component: SecurityAccessRequestForm
      },
      {
        path: 'change-password',
        name: 'ChangePasswordMain',
        component: ChangePassword
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation Guards
router.beforeEach((to, from, next) => {
  const isAuthenticated = authService.isAuthenticated()

  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!isAuthenticated) {
      next({ name: 'Login' })
    } else {
      next()
    }
  } else if (to.matched.some(record => record.meta.guest)) {
    if (isAuthenticated) {
      next({ name: 'Dashboard' })
    } else {
      next()
    }
  } else {
    next()
  }
})

export default router
