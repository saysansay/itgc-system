<template>
  <div class="change-requests">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Change Requests</h2>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-success" @click="exportToExcel">
          <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
        </button>
        <router-link to="/change-requests/create" class="btn btn-primary">
          <i class="bi bi-plus-circle me-2"></i>New Change Request
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
              placeholder="Search..."
              v-model="filters.search"
              @input="loadData"
            />
          </div>
          <div class="col-md-2">
            <Select2
              v-model="filters.status"
              :options="statusOptions"
              placeholder="All Status"
              @update:modelValue="loadData"
            />
          </div>
          <div class="col-md-2">
            <Select2
              v-model="filters.risk_level"
              :options="riskLevelOptions"
              placeholder="All Risk Levels"
              @update:modelValue="loadData"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>

        <div v-else>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Ticket Number</th>
                  <th>Title</th>
                  <th>System</th>
                  <th>Risk Level</th>
                  <th>Status</th>
                  <th>Requester</th>
                  <th>Created</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in changeRequests" :key="item.id">
                  <td>{{ item.ticket_number }}</td>
                  <td>{{ item.title }}</td>
                  <td>{{ item.system.name }}</td>
                  <td>
                    <span class="badge" :class="getRiskLevelClass(item.risk_level)">
                      {{ item.risk_level }}
                    </span>
                  </td>
                  <td>
                    <span class="badge" :class="getStatusClass(item.status)">
                      {{ formatStatus(item.status) }}
                    </span>
                  </td>
                  <td>{{ item.requester.name }}</td>
                  <td>{{ formatDate(item.created_at) }}</td>
                  <td>
                    <router-link
                      :to="`/change-requests/${item.id}`"
                      class="btn btn-sm btn-outline-primary me-1"
                    >
                      <i class="bi bi-eye"></i>
                    </router-link>
                    <router-link
                      :to="`/change-requests/${item.id}/edit`"
                      class="btn btn-sm btn-outline-secondary me-1"
                      v-if="item.status === 'draft'"
                    >
                      <i class="bi bi-pencil"></i>
                    </router-link>
                    <button
                      class="btn btn-sm btn-outline-danger"
                      @click="handleDelete(item.id)"
                      v-if="item.status === 'draft'"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </td>
                </tr>
                <tr v-if="changeRequests.length === 0">
                  <td colspan="8" class="text-center text-muted py-4">
                    No change requests found
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="d-flex justify-content-between align-items-center mt-3" v-if="meta.total > 0">
            <div>
              Showing {{ ((meta.current_page - 1) * meta.per_page) + 1 }} to 
              {{ Math.min(meta.current_page * meta.per_page, meta.total) }} of {{ meta.total }} entries
            </div>
            <nav>
              <ul class="pagination mb-0">
                <li class="page-item" :class="{ disabled: meta.current_page === 1 }">
                  <a class="page-link" href="#" @click.prevent="loadData(meta.current_page - 1)">Previous</a>
                </li>
                <li
                  v-for="page in visiblePages"
                  :key="page"
                  class="page-item"
                  :class="{ active: meta.current_page === page }"
                >
                  <a class="page-link" href="#" @click.prevent="loadData(page)">{{ page }}</a>
                </li>
                <li class="page-item" :class="{ disabled: meta.current_page === meta.last_page }">
                  <a class="page-link" href="#" @click.prevent="loadData(meta.current_page + 1)">Next</a>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import changeRequestService from '@/services/changeRequestService'
import { confirmAction, notifyError } from '@/utils/alerts'
import { downloadBlob } from '@/utils/download'

const loading = ref(false)
const changeRequests = ref([])
const filters = ref({
  search: '',
  status: '',
  risk_level: ''
})
const statusOptions = [
  { value: '', label: 'All Status' },
  { value: 'draft', label: 'Draft' },
  { value: 'pending_approval', label: 'Pending Approval' },
  { value: 'approved', label: 'Approved' },
  { value: 'rejected', label: 'Rejected' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'completed', label: 'Completed' },
  { value: 'failed', label: 'Failed' }
]
const riskLevelOptions = [
  { value: '', label: 'All Risk Levels' },
  { value: 'low', label: 'Low' },
  { value: 'medium', label: 'Medium' },
  { value: 'high', label: 'High' }
]
const meta = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, meta.value.current_page - 2)
  const end = Math.min(meta.value.last_page, meta.value.current_page + 2)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  return pages
})

const buildParams = (extra = {}) => {
  const params = {
    ...filters.value,
    ...extra
  }

  return Object.entries(params).reduce((acc, [key, value]) => {
    if (value !== '' && value !== null && value !== undefined) {
      acc[key] = value
    }
    return acc
  }, {})
}

const loadData = async (page = 1) => {
  loading.value = true
  try {
    const params = buildParams({
      page,
      per_page: meta.value.per_page
    })
    const response = await changeRequestService.getAll(params)
    
    if (response.success) {
      changeRequests.value = response.data
      meta.value = response.meta
    }
  } catch (error) {
    console.error('Failed to load change requests:', error)
  } finally {
    loading.value = false
  }
}

const exportToExcel = async () => {
  try {
    const params = buildParams()
    const response = await changeRequestService.exportChangeRequests(params)
    downloadBlob(response.data, 'change-requests.xlsx')
  } catch (error) {
    notifyError('Failed to export change requests')
  }
}

const handleDelete = async (id) => {
  const confirmed = await confirmAction('Are you sure you want to delete this change request?')
  if (!confirmed) return

  try {
    await changeRequestService.delete(id)
    loadData(meta.value.current_page)
  } catch (error) {
    notifyError('Failed to delete change request')
  }
}

const getRiskLevelClass = (level) => {
  const classes = {
    low: 'bg-success',
    medium: 'bg-warning',
    high: 'bg-danger'
  }
  return classes[level] || 'bg-secondary'
}

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-secondary',
    pending_approval: 'bg-warning',
    approved: 'bg-info',
    rejected: 'bg-danger',
    in_progress: 'bg-primary',
    completed: 'bg-success',
    failed: 'bg-danger'
  }
  return classes[status] || 'bg-secondary'
}

const formatStatus = (status) => {
  return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return dateString
  const pad = (value) => String(value).padStart(2, '0')
  const yyyy = date.getFullYear()
  const mm = pad(date.getMonth() + 1)
  const dd = pad(date.getDate())
  return `${yyyy}/${mm}/${dd}`
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.table th {
  white-space: nowrap;
}

.badge {
  text-transform: uppercase;
  font-size: 11px;
  font-weight: 600;
}
</style>
