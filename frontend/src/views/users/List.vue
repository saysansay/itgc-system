<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 mb-0">User Management</h2>
      <router-link :to="{ name: 'CreateUser' }" class="btn btn-danger">
        <i class="bi bi-plus-circle me-2"></i>Add User
      </router-link>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <input 
              type="text" 
              class="form-control" 
              placeholder="Search by name, email, or employee ID..."
              v-model="filters.search"
              @input="debouncedSearch"
            >
          </div>
          <div class="col-md-3">
            <Select2
              v-model="filters.role_id"
              :options="roles"
              label-key="name"
              value-key="id"
              placeholder="All Roles"
              @update:modelValue="loadUsers"
            />
          </div>
          <div class="col-md-3">
            <Select2
              v-model="filters.department_id"
              :options="departments"
              label-key="name"
              value-key="id"
              placeholder="All Departments"
              @update:modelValue="loadUsers"
            />
          </div>
          <div class="col-md-2">
            <Select2
              v-model="filters.is_active"
              :options="statusOptions"
              placeholder="All Status"
              @update:modelValue="loadUsers"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-danger" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>

        <div v-else-if="users.length === 0" class="text-center py-5 text-muted">
          <i class="bi bi-people fs-1 d-block mb-3"></i>
          <p>No users found</p>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id">
                <td>{{ user.employee_id }}</td>
                <td>
                  <div class="fw-semibold">{{ user.name }}</div>
                  <small class="text-muted">{{ user.position || '-' }}</small>
                </td>
                <td>{{ user.email }}</td>
                <td>{{ user.department?.name || '-' }}</td>
                <td>
                  <span 
                    v-for="role in user.roles" 
                    :key="role.id" 
                    class="badge bg-secondary me-1"
                  >
                    {{ role.name }}
                  </span>
                </td>
                <td>
                  <span 
                    class="badge" 
                    :class="user.is_active ? 'bg-success' : 'bg-secondary'"
                  >
                    {{ user.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <router-link 
                      :to="{ name: 'EditUser', params: { id: user.id } }" 
                      class="btn btn-outline-primary"
                      title="Edit"
                    >
                      <i class="bi bi-pencil"></i>
                    </router-link>
                    <button 
                      class="btn btn-outline-danger" 
                      @click="confirmDelete(user)"
                      title="Delete"
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
import userService from '@/services/userService'
import { confirmAction, notifyError, notifySuccess } from '@/utils/alerts'

export default {
  name: 'UserList',
  setup() {
    const router = useRouter()
    const loading = ref(false)
    const users = ref([])
    const roles = ref([])
    const departments = ref([])
    const pagination = reactive({
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0
    })
    const filters = reactive({
      search: '',
      role_id: '',
      department_id: '',
      is_active: ''
    })

    const statusOptions = [
      { value: '', label: 'All Status' },
      { value: '1', label: 'Active' },
      { value: '0', label: 'Inactive' }
    ]

    let searchTimeout = null

    const debouncedSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        loadUsers()
      }, 500)
    }

    const loadUsers = async () => {
      loading.value = true
      try {
        const params = {
          page: pagination.current_page,
          per_page: pagination.per_page
        }
        Object.entries(filters).forEach(([key, value]) => {
          if (value !== '' && value !== null && value !== undefined) {
            params[key] = value
          }
        })
        const { data } = await userService.getUsers(params)
        users.value = data.data
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.total = data.total
      } catch (error) {
        console.error('Error loading users:', error)
        notifyError('Failed to load users')
      } finally {
        loading.value = false
      }
    }

    const loadRoles = async () => {
      try {
        const { data } = await userService.getRoles()
        roles.value = data
      } catch (error) {
        console.error('Error loading roles:', error)
      }
    }

    const loadDepartments = async () => {
      try {
        const { data } = await userService.getDepartments()
        departments.value = data
      } catch (error) {
        console.error('Error loading departments:', error)
      }
    }

    const changePage = (page) => {
      if (page >= 1 && page <= pagination.last_page) {
        pagination.current_page = page
        loadUsers()
      }
    }

    const confirmDelete = async (user) => {
      const confirmed = await confirmAction(`Are you sure you want to delete ${user.name}?`)
      if (!confirmed) return

      try {
        await userService.deleteUser(user.id)
        notifySuccess('User deleted successfully')
        loadUsers()
      } catch (error) {
        console.error('Error deleting user:', error)
        notifyError(error.response?.data?.message || 'Failed to delete user')
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
      loadUsers()
      loadRoles()
      loadDepartments()
    })

    return {
      loading,
      users,
      roles,
      departments,
      pagination,
      filters,
      statusOptions,
      debouncedSearch,
      loadUsers,
      changePage,
      confirmDelete,
      visiblePages
    }
  }
}
</script>
