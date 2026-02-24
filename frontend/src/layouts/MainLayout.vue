<template>
  <div class="main-layout">
    <div class="layout-container">
      <!-- Sidebar / Navigation Pane -->
      <aside class="sidebar" :class="{ collapsed: sidebarCollapsed }">
        <div class="sidebar-content">
          <div class="sidebar-header">
            <div class="brand-block" v-if="!sidebarCollapsed">
              <h5>ITGC System</h5>
              <small>Oracle Style</small>
            </div>
            <button class="sidebar-toggle" type="button" @click="toggleSidebar">
              <i class="bi" :class="sidebarCollapsed ? 'bi-layout-sidebar-inset' : 'bi-list'"></i>
            </button>
          </div>
          <ul class="nav flex-column">
            <li class="nav-item">
              <router-link to="/dashboard" class="nav-link" active-class="active">
                <i class="bi bi-speedometer2"></i>
                <span v-if="!sidebarCollapsed">Dashboard</span>
              </router-link>
            </li>
            
            <!-- Master Data -->
            <li class="nav-group">
              <button
                v-if="!sidebarCollapsed"
                class="nav-group-toggle"
                type="button"
                @click="toggleGroup('master')"
              >
                <span class="nav-group-title">Master Data</span>
                <i
                  class="bi ms-auto"
                  :class="openGroups.master ? 'bi-chevron-up' : 'bi-chevron-down'"
                ></i>
              </button>
              <ul class="nav-group-list" v-show="openGroups.master || sidebarCollapsed">
                <li class="nav-item">
                  <router-link to="/users" class="nav-link" active-class="active">
                    <i class="bi bi-people"></i>
                    <span v-if="!sidebarCollapsed">Users</span>
                  </router-link>
                </li>
                <li class="nav-item">
                  <router-link to="/roles" class="nav-link" active-class="active">
                    <i class="bi bi-shield-check"></i>
                    <span v-if="!sidebarCollapsed">Roles</span>
                  </router-link>
                </li>
                <li class="nav-item">
                  <router-link to="/systems" class="nav-link" active-class="active">
                    <i class="bi bi-hdd-network"></i>
                    <span v-if="!sidebarCollapsed">Systems</span>
                  </router-link>
                </li>
                <li class="nav-item">
                  <router-link to="/departments" class="nav-link" active-class="active">
                    <i class="bi bi-building"></i>
                    <span v-if="!sidebarCollapsed">Departments</span>
                  </router-link>
                </li>
              </ul>
            </li>

            <!-- ITGC Modules -->
            <li class="nav-group">
              <button
                v-if="!sidebarCollapsed"
                class="nav-group-toggle"
                type="button"
                @click="toggleGroup('itgc')"
              >
                <span class="nav-group-title">ITGC Modules</span>
                <i
                  class="bi ms-auto"
                  :class="openGroups.itgc ? 'bi-chevron-up' : 'bi-chevron-down'"
                ></i>
              </button>
              <ul class="nav-group-list" v-show="openGroups.itgc || sidebarCollapsed">
                <li class="nav-item">
                  <router-link to="/it-assets" class="nav-link" active-class="active">
                    <i class="bi bi-laptop"></i>
                    <span v-if="!sidebarCollapsed">IT Assets</span>
                  </router-link>
                </li>
                <li class="nav-item">
                  <router-link to="/usb-loans" class="nav-link" active-class="active">
                    <i class="bi bi-usb-drive"></i>
                    <span v-if="!sidebarCollapsed">Peminjaman USB</span>
                  </router-link>
                </li>
                <li class="nav-item">
                  <router-link to="/admin-access-requests" class="nav-link" active-class="active">
                    <i class="bi bi-shield-lock"></i>
                    <span v-if="!sidebarCollapsed">Peminjaman Akses Administrator</span>
                  </router-link>
                </li>
                <li class="nav-item">
                  <router-link to="/general-troubles" class="nav-link" active-class="active">
                    <i class="bi bi-exclamation-diamond"></i>
                    <span v-if="!sidebarCollapsed">General Trouble</span>
                  </router-link>
                </li>
                <li class="nav-item">
                  <router-link to="/security-access-requests" class="nav-link" active-class="active">
                    <i class="bi bi-shield-lock"></i>
                    <span v-if="!sidebarCollapsed">Security Access</span>
                  </router-link>
                </li>
                <li class="nav-item">
                  <router-link to="/audit-logs" class="nav-link" active-class="active">
                    <i class="bi bi-clipboard-data"></i>
                    <span v-if="!sidebarCollapsed">Audit Logs</span>
                  </router-link>
                </li>
              </ul>
            </li>
          </ul>

          <div class="sidebar-footer">
            <router-link
              to="/change-password"
              class="account-btn"
              :class="{ collapsed: sidebarCollapsed }"
            >
              <i class="bi bi-key"></i>
              <span v-if="!sidebarCollapsed">Change Password</span>
            </router-link>
            <button class="logout-btn" type="button" @click="handleLogout">
              <i class="bi bi-box-arrow-right"></i>
              <span v-if="!sidebarCollapsed">Logout</span>
            </button>
          </div>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="main-content">
        <nav class="oracle-breadcrumb" aria-label="breadcrumb">
          <ol class="breadcrumb mb-3">
            <li class="breadcrumb-item" v-for="(item, index) in breadcrumbs" :key="item.to">
              <router-link v-if="index < breadcrumbs.length - 1" :to="item.to">
                {{ item.label }}
              </router-link>
              <span v-else>{{ item.label }}</span>
            </li>
          </ol>
        </nav>
        <router-view />
      </main>
    </div>

    <!-- Footer -->
    <footer class="footer" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
      <div class="container-fluid">
        <div class="text-center text-muted">
          <small>&copy; {{ new Date().getFullYear() }} ITGC System. All rights reserved.</small>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import authService from '@/services/authService'
import { confirmAction } from '@/utils/alerts'

const router = useRouter()
const route = useRoute()
const sidebarCollapsed = ref(false)
const user = ref(null)

const breadcrumbs = computed(() => {
  const path = route.path || ''
  const segments = path.split('/').filter(Boolean)
  const items = [{ label: 'Dashboard', to: '/dashboard' }]
  let currentPath = ''

  segments.forEach((segment) => {
    currentPath += `/${segment}`
    const label = segment
      .replace(/-/g, ' ')
      .replace(/\b\w/g, (char) => char.toUpperCase())
    items.push({ label, to: currentPath })
  })

  return items
})

const openGroups = ref({
  master: true,
  itgc: true
})

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

const toggleGroup = (key) => {
  openGroups.value[key] = !openGroups.value[key]
}

const groupRoutes = {
  master: ['/users', '/roles', '/systems', '/departments'],
  itgc: [
    '/it-assets',
    '/usb-loans',
    '/admin-access-requests',
    '/general-troubles',
    '/security-access-requests',
    '/audit-logs'
  ]
}

const ensureActiveGroupOpen = (path) => {
  const match = Object.entries(groupRoutes).find(([, routes]) =>
    routes.some((routePath) => path.startsWith(routePath))
  )

  if (match) {
    const [groupKey] = match
    openGroups.value[groupKey] = true
  }
}

const handleLogout = async () => {
  const confirmed = await confirmAction(
    'Anda yakin ingin keluar dari aplikasi?',
    'Konfirmasi Logout',
    {
      confirmButtonText: 'Logout',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#c74634'
    }
  )
  if (!confirmed) return
  await authService.logout()
  router.push('/auth/login')
}

onMounted(() => {
  user.value = authService.getUser()
  ensureActiveGroupOpen(route.path)
})

watch(
  () => route.path,
  (path) => {
    ensureActiveGroupOpen(path)
  }
)
</script>

<style scoped>
/* ============================================
   Dynamics 365 Inspired Layout
   ============================================ */

.main-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: var(--d365-neutral-light);
}

/* ============================================
   Topbar (Command Bar Style)
   ============================================ */

.topbar {
  height: 48px;
  background-color: var(--d365-primary);
  color: var(--d365-text-white);
  padding: 0 var(--d365-space-lg);
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.topbar h4 {
  font-size: 16px;
  font-weight: 600;
  margin: 0;
  color: var(--d365-text-white);
  letter-spacing: 0.3px;
}

.topbar .btn-link {
  text-decoration: none;
  padding: 6px;
  border-radius: 10px;
  transition: var(--d365-transition);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--d365-text-white);
  background: transparent;
  border: none;
}

.topbar .btn-link:hover {
  background-color: rgba(255, 255, 255, 0.12);
  color: var(--d365-text-white) !important;
}

.topbar .btn-link i {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.12);
  color: #ffffff;
}

.topbar .btn-link:hover i {
  background: rgba(255, 255, 255, 0.2);
}

.topbar .dropdown-toggle {
  background: transparent;
  border: none;
  color: var(--d365-text-white);
  font-size: 14px;
  font-weight: 500;
  padding: 6px 12px;
  border-radius: var(--d365-radius-sm);
  transition: var(--d365-transition);
}

.topbar .dropdown-toggle:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.topbar .dropdown-toggle::after {
  margin-left: 8px;
  vertical-align: middle;
}

/* ============================================
   Layout Container
   ============================================ */

.layout-container {
  display: flex;
  margin-top: 0;
  min-height: 100vh;
}

/* ============================================
   Sidebar (Navigation Pane)
   ============================================ */

.sidebar {
  width: 240px;
  background: linear-gradient(180deg, var(--sidebar-bg), #171a20);
  border-right: 1px solid rgba(255, 255, 255, 0.08);
  transition: width 0.25s ease;
  position: fixed;
  left: 0;
  top: 0;
  bottom: 0;
  overflow-y: auto;
  overflow-x: hidden;
  z-index: 999;
  font-family: "IBM Plex Sans", "Segoe UI", Arial, sans-serif;
}

.sidebar.collapsed {
  width: 84px;
}

.sidebar-content {
  display: flex;
  flex-direction: column;
  padding: var(--d365-space-md) var(--d365-space-sm);
  min-height: calc(100vh - 24px);
}

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  padding: 0.5rem 0.6rem 0.9rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  margin-bottom: 0.75rem;
}

.brand-block h5 {
  margin: 0;
  font-weight: 700;
  font-size: 0.95rem;
  color: #ffffff;
  letter-spacing: 0.02em;
}

.brand-block small {
  color: #bec7d2;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  font-size: 0.6rem;
  font-family: "IBM Plex Mono", "Segoe UI", Arial, sans-serif;
}

.sidebar-toggle {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  border: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.12);
  color: #ffffff;
  transition: var(--d365-transition);
}

.sidebar-toggle:hover {
  background: rgba(255, 255, 255, 0.2);
}

/* Navigation Headers */
.nav-header {
  padding: var(--d365-space-md) var(--d365-space-md) var(--d365-space-xs);
  margin-top: var(--d365-space-sm);
  transition: var(--d365-transition);
}

.nav-header small {
  font-size: 11px;
  font-weight: 600;
  color: var(--d365-text-secondary);
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.sidebar.collapsed .nav-header {
  padding: var(--d365-space-xs);
  text-align: center;
}

/* Navigation Groups */
.nav-group {
  margin-bottom: var(--d365-space-xs);
}

.nav-group-toggle {
  width: 100%;
  background: transparent;
  border: none;
  display: flex;
  align-items: center;
  padding: 10px 12px;
  font-size: 10px;
  font-weight: 600;
  color: #cfd6df;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  transition: var(--d365-transition);
  font-family: "IBM Plex Mono", "Segoe UI", Arial, sans-serif;
}

.nav-group-toggle:hover {
  color: #ffffff;
}

.nav-group-list {
  list-style: none;
  padding-left: 0;
  margin: 0 0 var(--d365-space-xs);
}

/* Navigation Links */
.nav-item {
  margin-bottom: 4px;
}

.nav-link {
  color: #e6eaf0;
  padding: 8px 10px;
  display: flex;
  align-items: center;
  text-decoration: none;
  transition: var(--d365-transition);
  white-space: nowrap;
  font-size: 12px;
  font-weight: 500;
  border-radius: 10px;
  border-left: 0;
  position: relative;
  letter-spacing: 0.008em;
}

.nav-link:hover {
  background-color: rgba(199, 70, 52, 0.18);
  color: #ffffff;
}

.nav-link.active {
  background-color: rgba(199, 70, 52, 0.28);
  color: #ffffff;
  font-weight: 600;
  box-shadow: inset 0 0 0 1px rgba(199, 70, 52, 0.35);
}

.nav-link i {
  font-size: 14px;
  min-width: 24px;
  width: 24px;
  height: 24px;
  margin-right: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.08);
  color: #ffb7ac;
}

.nav-link:hover i,
.nav-link.active i {
  background: rgba(199, 70, 52, 0.35);
  color: #ffffff;
}

.sidebar.collapsed .nav-link {
  padding: 8px;
  justify-content: center;
}

.sidebar.collapsed .nav-link i {
  margin-right: 0;
  font-size: 14px;
}

.sidebar.collapsed .nav-link span {
  display: none;
}

.sidebar-footer {
  margin-top: auto;
  padding: 0.4rem 0.2rem 0.6rem;
}

.logout-btn {
  width: 100%;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #f5f7fb;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 10px 12px;
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 0.02em;
  transition: var(--d365-transition);
}

.account-btn {
  width: 100%;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #f0f3f7;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 10px 12px;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.02em;
  text-decoration: none;
  transition: var(--d365-transition);
  margin-bottom: 0.5rem;
}

.account-btn i {
  font-size: 14px;
}

.account-btn:hover {
  background: rgba(255, 255, 255, 0.16);
  color: #ffffff;
}

.sidebar.collapsed .account-btn {
  padding: 8px;
}

.logout-btn i {
  font-size: 16px;
}

.logout-btn:hover {
  background: rgba(199, 70, 52, 0.22);
  border-color: rgba(199, 70, 52, 0.35);
  color: #ffffff;
}

.sidebar.collapsed .logout-btn {
  padding: 10px;
}

/* ============================================
   Main Content Area
   ============================================ */

.main-content {
  flex: 1;
  margin-left: 240px;
  padding: var(--d365-space-lg);
  background-color: var(--d365-neutral-light);
  transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  min-height: 100vh;
}

.sidebar.collapsed ~ .main-content {
  margin-left: 84px;
}

/* ============================================
   Footer
   ============================================ */

.footer {
  height: 40px;
  background-color: var(--d365-neutral-lightest);
  border-top: 1px solid var(--d365-border);
  display: flex;
  align-items: center;
  position: fixed;
  bottom: 0;
  left: 220px;
  right: 0;
  z-index: 998;
  padding: 0 var(--d365-space-lg);
  transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.footer.sidebar-collapsed {
  left: 48px;
}

.footer .text-muted {
  font-size: 12px;
  color: var(--d365-text-secondary);
}

/* ============================================
   Dropdown Menu Customization
   ============================================ */

.dropdown-menu {
  border: 1px solid var(--d365-border);
  border-radius: var(--d365-radius-md);
  box-shadow: var(--d365-shadow-lg);
  padding: var(--d365-space-sm);
  margin-top: 8px;
}

.dropdown-item {
  padding: 8px 12px;
  font-size: 14px;
  color: var(--d365-text-primary);
  border-radius: var(--d365-radius-sm);
  transition: var(--d365-transition);
  display: flex;
  align-items: center;
}

.dropdown-item i {
  font-size: 16px;
  width: 20px;
}

.dropdown-item:hover {
  background-color: var(--d365-neutral-lighter);
  color: var(--d365-text-primary);
}

.dropdown-divider {
  margin: var(--d365-space-xs) 0;
  border-color: var(--d365-border);
}

/* ============================================
   Bootstrap Icons Import
   ============================================ */

@import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css");

/* ============================================
   Responsive Design
   ============================================ */

@media (max-width: 1024px) {
  .sidebar {
    width: 84px;
  }
  
  .main-content {
    margin-left: 84px;
  }
  
  .footer {
    left: 48px;
  }
  
  .nav-header {
    display: none;
  }

  .nav-group-toggle {
    display: none;
  }
  
  .nav-link {
    padding: 12px;
    justify-content: center;
  }
  
  .nav-link i {
    margin-right: 0;
    font-size: 20px;
  }
  
  .nav-link span {
    display: none;
  }
}

@media (max-width: 768px) {
  .topbar {
    padding: 0 var(--d365-space-md);
  }
  
  .topbar h4 {
    font-size: 14px;
  }
  
  .main-content {
    padding: var(--d365-space-md);
  }
}

/* ============================================
   Scroll Animation
   ============================================ */

.sidebar::-webkit-scrollbar {
  width: 6px;
}

.sidebar::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar::-webkit-scrollbar-thumb {
  background: var(--d365-neutral-tertiary);
  border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
  background: var(--d365-neutral-secondary);
}
</style>
