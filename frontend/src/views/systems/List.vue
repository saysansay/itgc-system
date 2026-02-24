<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 mb-0">System Management</h2>
      <router-link :to="{ name: 'CreateSystem' }" class="btn btn-danger">
        <i class="bi bi-plus-circle me-2"></i>Add System
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
              placeholder="Search by name, code, or description..."
              v-model="filters.search"
              @input="debouncedSearch"
            >
          </div>
          <div class="col-md-4">
            <Select2
              v-model="filters.category"
              :options="categoryOptions"
              placeholder="All Categories"
              @update:modelValue="loadSystems"
            />
          </div>
          <div class="col-md-4">
            <Select2
              v-model="filters.is_active"
              :options="statusOptions"
              placeholder="All Status"
              @update:modelValue="loadSystems"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Systems Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-danger" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>

        <div v-else-if="systems.length === 0" class="text-center py-5 text-muted">
          <i class="bi bi-hdd-network fs-1 d-block mb-3"></i>
          <p>No systems found</p>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Code</th>
                <th>System Name</th>
                <th>Category</th>
                <th>Version</th>
                <th>Vendor</th>
                <th>Owner</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="system in systems" :key="system.id">
                <td><code>{{ system.code }}</code></td>
                <td>
                  <div class="fw-semibold">{{ system.name }}</div>
                  <small class="text-muted">{{ system.description || '-' }}</small>
                </td>
                <td>
                  <span class="badge" :class="getCategoryClass(system.category)">
                    {{ system.category.toUpperCase() }}
                  </span>
                </td>
                <td>{{ system.version || '-' }}</td>
                <td>{{ system.vendor || '-' }}</td>
                <td>{{ system.owner || '-' }}</td>
                <td>
                  <span 
                    class="badge" 
                    :class="system.is_active ? 'bg-success' : 'bg-secondary'"
                  >
                    {{ system.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <a 
                      v-if="system.url" 
                      :href="system.url" 
                      target="_blank" 
                      class="btn btn-outline-info"
                      title="Open System"
                    >
                      <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                    <router-link 
                      :to="{ name: 'EditSystem', params: { id: system.id } }" 
                      class="btn btn-outline-primary"
                      title="Edit"
                    >
                      <i class="bi bi-pencil"></i>
                    </router-link>
                    <button 
                      class="btn btn-outline-danger" 
                      @click="confirmDelete(system)"
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
import systemService from '@/services/systemService'
import { confirmAction, notifyError, notifySuccess } from '@/utils/alerts'

export default {
  name: 'SystemList',
  setup() {
    const router = useRouter()
    const loading = ref(false)
    const systems = ref([])
    const pagination = reactive({
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0
    })
    const filters = reactive({
      search: '',
      category: '',
      is_active: ''
    })

    const categoryOptions = [
      { value: '', label: 'All Categories' },
      { value: 'erp', label: 'ERP' },
      { value: 'crm', label: 'CRM' },
      { value: 'hrms', label: 'HRMS' },
      { value: 'financial', label: 'Financial' },
      { value: 'inventory', label: 'Inventory' },
      { value: 'other', label: 'Other' }
    ]

    const statusOptions = [
      { value: '', label: 'All Status' },
      { value: '1', label: 'Active' },
      { value: '0', label: 'Inactive' }
    ]

    let searchTimeout = null

    const debouncedSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        loadSystems()
      }, 500)
    }

    const getCategoryClass = (category) => {
      const classes = {
        erp: 'bg-primary',
        crm: 'bg-success',
        hrms: 'bg-info',
        financial: 'bg-warning',
        inventory: 'bg-secondary',
        other: 'bg-dark'
      }
      return classes[category] || 'bg-secondary'
    }

    const loadSystems = async () => {
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
        const { data } = await systemService.getSystems(params)
        systems.value = data.data
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.total = data.total
      } catch (error) {
        console.error('Error loading systems:', error)
        notifyError('Failed to load systems')
      } finally {
        loading.value = false
      }
    }

    const changePage = (page) => {
      if (page >= 1 && page <= pagination.last_page) {
        pagination.current_page = page
        loadSystems()
      }
    }

    const confirmDelete = async (system) => {
      const confirmed = await confirmAction(`Are you sure you want to delete "${system.name}"?`)
      if (!confirmed) return

      try {
        await systemService.deleteSystem(system.id)
        notifySuccess('System deleted successfully')
        loadSystems()
      } catch (error) {
        console.error('Error deleting system:', error)
        notifyError(error.response?.data?.message || 'Failed to delete system')
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
      loadSystems()
    })

    return {
      loading,
      systems,
      pagination,
      filters,
      categoryOptions,
      statusOptions,
      debouncedSearch,
      getCategoryClass,
      loadSystems,
      changePage,
      confirmDelete,
      visiblePages
    }
  }
}
</script>
