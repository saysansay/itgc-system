<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 mb-0">Role Management</h2>
      <router-link :to="{ name: 'CreateRole' }" class="btn btn-danger">
        <i class="bi bi-plus-circle me-2"></i>Add Role
      </router-link>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <input 
              type="text" 
              class="form-control" 
              placeholder="Search by name or description..."
              v-model="filters.search"
              @input="debouncedSearch"
            >
          </div>
        </div>
      </div>
    </div>

    <!-- Roles Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-danger" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>

        <div v-else-if="roles.length === 0" class="text-center py-5 text-muted">
          <i class="bi bi-shield-check fs-1 d-block mb-3"></i>
          <p>No roles found</p>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Role Name</th>
                <th>Description</th>
                <th>Users Count</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="role in roles" :key="role.id">
                <td>
                  <strong>{{ role.name }}</strong>
                </td>
                <td>{{ role.description || '-' }}</td>
                <td>
                  <span class="badge bg-info">{{ role.users_count }} users</span>
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <router-link 
                      :to="{ name: 'EditRole', params: { id: role.id } }" 
                      class="btn btn-outline-primary"
                      title="Edit"
                    >
                      <i class="bi bi-pencil"></i>
                    </router-link>
                    <button 
                      class="btn btn-outline-danger" 
                      @click="confirmDelete(role)"
                      title="Delete"
                      :disabled="role.users_count > 0"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <nav v-if="pagination.last_page > 1" class="mt-4">
          <ul class="pagination justify-content-center">
            <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
              <a class="page-link" @click.prevent="changePage(pagination.current_page - 1)">
                Previous
              </a>
            </li>
            <li 
              v-for="page in visiblePages" 
              :key="page" 
              class="page-item" 
              :class="{ active: page === pagination.current_page }"
            >
              <a class="page-link" @click.prevent="changePage(page)">{{ page }}</a>
            </li>
            <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
              <a class="page-link" @click.prevent="changePage(pagination.current_page + 1)">
                Next
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import roleService from '@/services/roleService'
import { confirmAction, notifyError, notifyInfo, notifySuccess } from '@/utils/alerts'

export default {
  name: 'RoleList',
  setup() {
    const router = useRouter()
    const loading = ref(false)
    const roles = ref([])
    const pagination = reactive({
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0
    })
    const filters = reactive({
      search: ''
    })

    let searchTimeout = null

    const debouncedSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        loadRoles()
      }, 500)
    }

    const loadRoles = async () => {
      loading.value = true
      try {
        const params = {
          page: pagination.current_page,
          per_page: pagination.per_page,
          ...filters
        }
        const { data } = await roleService.getRoles(params)
        roles.value = data.data
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.total = data.total
      } catch (error) {
        console.error('Error loading roles:', error)
        notifyError('Failed to load roles')
      } finally {
        loading.value = false
      }
    }

    const changePage = (page) => {
      if (page >= 1 && page <= pagination.last_page) {
        pagination.current_page = page
        loadRoles()
      }
    }

    const confirmDelete = async (role) => {
      if (role.users_count > 0) {
        notifyInfo(`Cannot delete role with ${role.users_count} assigned user(s)`)
        return
      }

      const confirmed = await confirmAction(`Are you sure you want to delete role "${role.name}"?`)
      if (!confirmed) return

      try {
        await roleService.deleteRole(role.id)
        notifySuccess('Role deleted successfully')
        loadRoles()
      } catch (error) {
        console.error('Error deleting role:', error)
        notifyError(error.response?.data?.message || 'Failed to delete role')
      }
    }

    const visiblePages = computed(() => {
      const pages = []
      const current = pagination.current_page
      const last = pagination.last_page
      
      if (last <= 7) {
        for (let i = 1; i <= last; i++) {
          pages.push(i)
        }
      } else {
        if (current <= 4) {
          for (let i = 1; i <= 5; i++) pages.push(i)
          pages.push('...')
          pages.push(last)
        } else if (current >= last - 3) {
          pages.push(1)
          pages.push('...')
          for (let i = last - 4; i <= last; i++) pages.push(i)
        } else {
          pages.push(1)
          pages.push('...')
          for (let i = current - 1; i <= current + 1; i++) pages.push(i)
          pages.push('...')
          pages.push(last)
        }
      }
      
      return pages
    })

    onMounted(() => {
      loadRoles()
    })

    return {
      loading,
      roles,
      pagination,
      filters,
      debouncedSearch,
      loadRoles,
      changePage,
      confirmDelete,
      visiblePages
    }
  }
}
</script>
