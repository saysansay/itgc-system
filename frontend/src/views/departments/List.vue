<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 mb-0">Department Management</h2>
      <router-link :to="{ name: 'CreateDepartment' }" class="btn btn-danger">
        <i class="bi bi-plus-circle me-2"></i>Add Department
      </router-link>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-8">
            <input 
              type="text" 
              class="form-control" 
              placeholder="Search by name, code, or description..."
              v-model="filters.search"
              @input="debouncedSearch"
            >
          </div>
          <div class="col-md-4">
            <Select2
              v-model="filters.is_active"
              :options="statusOptions"
              placeholder="All Status"
              @update:modelValue="loadDepartments"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Departments Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-danger" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>

        <div v-else-if="departments.length === 0" class="text-center py-5 text-muted">
          <i class="bi bi-building fs-1 d-block mb-3"></i>
          <p>No departments found</p>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Code</th>
                <th>Department Name</th>
                <th>Manager</th>
                <th>Contact</th>
                <th>Users Count</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="department in departments" :key="department.id">
                <td><code>{{ department.code }}</code></td>
                <td>
                  <div class="fw-semibold">{{ department.name }}</div>
                  <small class="text-muted">{{ department.description || '-' }}</small>
                </td>
                <td>{{ department.manager || '-' }}</td>
                <td>
                  <div v-if="department.email">
                    <i class="bi bi-envelope me-1"></i>
                    <small>{{ department.email }}</small>
                  </div>
                  <div v-if="department.phone">
                    <i class="bi bi-telephone me-1"></i>
                    <small>{{ department.phone }}</small>
                  </div>
                  <span v-if="!department.email && !department.phone">-</span>
                </td>
                <td>
                  <span class="badge bg-info">{{ department.users_count }} users</span>
                </td>
                <td>
                  <span 
                    class="badge" 
                    :class="department.is_active ? 'bg-success' : 'bg-secondary'"
                  >
                    {{ department.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <router-link 
                      :to="{ name: 'EditDepartment', params: { id: department.id } }" 
                      class="btn btn-outline-primary"
                      title="Edit"
                    >
                      <i class="bi bi-pencil"></i>
                    </router-link>
                    <button 
                      class="btn btn-outline-danger" 
                      @click="confirmDelete(department)"
                      title="Delete"
                      :disabled="department.users_count > 0"
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
import departmentService from '@/services/departmentService'
import { confirmAction, notifyError, notifyInfo, notifySuccess } from '@/utils/alerts'

export default {
  name: 'DepartmentList',
  setup() {
    const router = useRouter()
    const loading = ref(false)
    const departments = ref([])
    const pagination = reactive({
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0
    })
    const filters = reactive({
      search: '',
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
        loadDepartments()
      }, 500)
    }

    const loadDepartments = async () => {
      loading.value = true
      try {
        const params = Object.entries({
          page: pagination.current_page,
          per_page: pagination.per_page,
          ...filters
        })
          .filter(([, value]) => value !== '' && value !== null && value !== undefined)
          .reduce((acc, [key, value]) => {
            acc[key] = value
            return acc
          }, {})
        const { data } = await departmentService.getDepartments(params)
        departments.value = data.data
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.total = data.total
      } catch (error) {
        console.error('Error loading departments:', error)
        notifyError('Failed to load departments')
      } finally {
        loading.value = false
      }
    }

    const changePage = (page) => {
      if (page >= 1 && page <= pagination.last_page) {
        pagination.current_page = page
        loadDepartments()
      }
    }

    const confirmDelete = async (department) => {
      if (department.users_count > 0) {
        notifyInfo(`Cannot delete department with ${department.users_count} assigned user(s)`)
        return
      }

      const confirmed = await confirmAction(`Are you sure you want to delete "${department.name}"?`)
      if (!confirmed) return

      try {
        await departmentService.deleteDepartment(department.id)
        notifySuccess('Department deleted successfully')
        loadDepartments()
      } catch (error) {
        console.error('Error deleting department:', error)
        notifyError(error.response?.data?.message || 'Failed to delete department')
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
      loadDepartments()
    })

    return {
      loading,
      departments,
      pagination,
      filters,
      statusOptions,
      debouncedSearch,
      loadDepartments,
      changePage,
      confirmDelete,
      visiblePages
    }
  }
}
</script>
