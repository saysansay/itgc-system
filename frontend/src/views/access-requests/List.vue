<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 mb-0">Access Request Management</h2>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-success" @click="exportToExcel">
          <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
        </button>
        <router-link :to="{ name: 'CreateAccessRequest' }" class="btn btn-danger">
          <i class="bi bi-plus-circle me-2"></i>New Request
        </router-link>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <input 
              type="text" 
              class="form-control" 
              placeholder="Search by ticket, purpose, or user..."
              v-model="filters.search"
              @input="debouncedSearch"
            >
          </div>
          <div class="col-md-3">
            <Select2
              v-model="filters.system_id"
              :options="systems"
              label-key="name"
              value-key="id"
              placeholder="All Systems"
              @update:modelValue="loadAccessRequests"
            />
          </div>
          <div class="col-md-3">
            <Select2
              v-model="filters.access_type"
              :options="accessTypeOptions"
              placeholder="All Access Types"
              @update:modelValue="loadAccessRequests"
            />
          </div>
          <div class="col-md-3">
            <Select2
              v-model="filters.status"
              :options="statusOptions"
              placeholder="All Status"
              @update:modelValue="loadAccessRequests"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Access Requests Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-danger" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>

        <div v-else-if="accessRequests.length === 0" class="text-center py-5 text-muted">
          <i class="bi bi-key fs-1 d-block mb-3"></i>
          <p>No access requests found</p>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Ticket #</th>
                <th>Requester</th>
                <th>System</th>
                <th>Access Type</th>
                <th>Access Level</th>
                <th>Purpose</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="request in accessRequests" :key="request.id">
                <td>
                  <router-link 
                    :to="{ name: 'AccessRequestDetail', params: { id: request.id } }"
                    class="text-decoration-none"
                  >
                    <code>{{ request.ticket_number }}</code>
                  </router-link>
                </td>
                <td>{{ request.user?.name }}</td>
                <td>{{ request.system?.name }}</td>
                <td>
                  <span class="badge" :class="getAccessTypeClass(request.access_type)">
                    {{ formatAccessType(request.access_type) }}
                  </span>
                </td>
                <td>
                  <span class="badge bg-dark">{{ request.access_level.toUpperCase() }}</span>
                </td>
                <td>
                  <small>{{ truncate(request.purpose, 50) }}</small>
                </td>
                <td>
                  <span class="badge" :class="getStatusClass(request.status)">
                    {{ request.status.toUpperCase() }}
                  </span>
                </td>
                <td>
                  <small>{{ formatDate(request.created_at) }}</small>
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <router-link 
                      :to="{ name: 'AccessRequestDetail', params: { id: request.id } }" 
                      class="btn btn-outline-info"
                      title="View Details"
                    >
                      <i class="bi bi-eye"></i>
                    </router-link>
                    <router-link 
                      v-if="request.status === 'pending' || request.status === 'rejected'"
                      :to="{ name: 'EditAccessRequest', params: { id: request.id } }" 
                      class="btn btn-outline-primary"
                      title="Edit"
                    >
                      <i class="bi bi-pencil"></i>
                    </router-link>
                    <button 
                      v-if="request.status === 'pending'"
                      class="btn btn-outline-danger" 
                      @click="confirmDelete(request)"
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
import accessRequestService from '@/services/accessRequestService'
import { confirmAction, notifyError, notifySuccess } from '@/utils/alerts'
import { downloadBlob } from '@/utils/download'

export default {
  name: 'AccessRequestList',
  setup() {
    const router = useRouter()
    const loading = ref(false)
    const accessRequests = ref([])
    const systems = ref([])
    const pagination = reactive({
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0
    })
    const filters = reactive({
      search: '',
      system_id: '',
      access_type: '',
      status: ''
    })

    const accessTypeOptions = [
      { value: '', label: 'All Access Types' },
      { value: 'new', label: 'New Access' },
      { value: 'modify', label: 'Modify Access' },
      { value: 'revoke', label: 'Revoke Access' },
      { value: 'temporary', label: 'Temporary Access' }
    ]

    const statusOptions = [
      { value: '', label: 'All Status' },
      { value: 'pending', label: 'Pending' },
      { value: 'approved', label: 'Approved' },
      { value: 'rejected', label: 'Rejected' },
      { value: 'implemented', label: 'Implemented' }
    ]

    let searchTimeout = null

    const debouncedSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        loadAccessRequests()
      }, 500)
    }

    const getAccessTypeClass = (type) => {
      const classes = {
        new: 'bg-success',
        modify: 'bg-warning',
        revoke: 'bg-danger',
        temporary: 'bg-info'
      }
      return classes[type] || 'bg-secondary'
    }

    const getStatusClass = (status) => {
      const classes = {
        pending: 'bg-warning',
        approved: 'bg-success',
        rejected: 'bg-danger',
        implemented: 'bg-primary'
      }
      return classes[status] || 'bg-secondary'
    }

    const formatAccessType = (type) => {
      const types = {
        new: 'New Access',
        modify: 'Modify',
        revoke: 'Revoke',
        temporary: 'Temporary'
      }
      return types[type] || type
    }

    const truncate = (text, length) => {
      if (!text) return '-'
      return text.length > length ? text.substring(0, length) + '...' : text
    }

    const formatDate = (date) => {
      const parsed = new Date(date)
      if (Number.isNaN(parsed.getTime())) return date
      const pad = (value) => String(value).padStart(2, '0')
      const yyyy = parsed.getFullYear()
      const mm = pad(parsed.getMonth() + 1)
      const dd = pad(parsed.getDate())
      return `${yyyy}/${mm}/${dd}`
    }

    const loadSystems = async () => {
      try {
        const { data } = await accessRequestService.getSystems()
        systems.value = data.data
      } catch (error) {
        console.error('Error loading systems:', error)
      }
    }

    const buildParams = (extra = {}) => {
      return Object.entries({
        ...filters,
        ...extra
      })
        .filter(([, value]) => value !== '' && value !== null && value !== undefined)
        .reduce((acc, [key, value]) => {
          acc[key] = value
          return acc
        }, {})
    }

    const loadAccessRequests = async () => {
      loading.value = true
      try {
        const params = buildParams({
          page: pagination.current_page,
          per_page: pagination.per_page
        })
        const { data } = await accessRequestService.getAccessRequests(params)
        accessRequests.value = data.data
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.total = data.total
      } catch (error) {
        console.error('Error loading access requests:', error)
        notifyError('Failed to load access requests')
      } finally {
        loading.value = false
      }
    }

    const changePage = (page) => {
      if (page >= 1 && page <= pagination.last_page) {
        pagination.current_page = page
        loadAccessRequests()
      }
    }

    const confirmDelete = async (request) => {
      const confirmed = await confirmAction(`Are you sure you want to delete access request ${request.ticket_number}?`)
      if (!confirmed) return

      try {
        await accessRequestService.deleteAccessRequest(request.id)
        notifySuccess('Access request deleted successfully')
        loadAccessRequests()
      } catch (error) {
        console.error('Error deleting access request:', error)
        notifyError(error.response?.data?.message || 'Failed to delete access request')
      }
    }

    const exportToExcel = async () => {
      try {
        const params = buildParams()
        const response = await accessRequestService.exportAccessRequests(params)
        downloadBlob(response.data, 'access-requests.xlsx')
      } catch (error) {
        notifyError('Failed to export access requests')
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
      loadAccessRequests()
    })

    return {
      loading,
      accessRequests,
      systems,
      pagination,
      filters,
      accessTypeOptions,
      statusOptions,
      debouncedSearch,
      getAccessTypeClass,
      getStatusClass,
      formatAccessType,
      truncate,
      formatDate,
      loadAccessRequests,
      changePage,
      confirmDelete,
      exportToExcel,
      visiblePages
    }
  }
}
</script>
