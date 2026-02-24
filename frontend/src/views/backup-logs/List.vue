<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 mb-0">Backup Logs</h2>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-success" @click="exportToExcel">
          <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
        </button>
        <router-link to="/backup-logs/create" class="btn btn-danger">
          <i class="bi bi-plus-circle me-2"></i>New Backup Log
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
              placeholder="Search system or location..."
              v-model="filters.search"
              @input="debouncedSearch"
            />
          </div>
          <div class="col-md-2">
            <Select2
              v-model="filters.system_id"
              :options="systems"
              label-key="name"
              value-key="id"
              placeholder="All Systems"
              @update:modelValue="loadBackupLogs"
            />
          </div>
          <div class="col-md-2">
            <Select2
              v-model="filters.backup_type"
              :options="backupTypeOptions"
              placeholder="All Types"
              @update:modelValue="loadBackupLogs"
            />
          </div>
          <div class="col-md-2">
            <Select2
              v-model="filters.status"
              :options="statusOptions"
              placeholder="All Status"
              @update:modelValue="loadBackupLogs"
            />
          </div>
          <div class="col-md-2">
            <Select2
              v-model="filters.is_verified"
              :options="verificationOptions"
              placeholder="All Verification"
              @update:modelValue="loadBackupLogs"
            />
          </div>
          <div class="col-md-1">
            <button class="btn btn-outline-secondary w-100" @click="resetFilters">
              <i class="bi bi-arrow-clockwise"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-danger" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>

        <div v-else>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>System</th>
                  <th>Type</th>
                  <th>Scheduled Time</th>
                  <th>Duration</th>
                  <th>Size</th>
                  <th>Status</th>
                  <th>Verified</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="log in backupLogs.data" :key="log.id">
                  <td>{{ log.system?.name }}</td>
                  <td>
                    <span class="badge" :class="getBackupTypeClass(log.backup_type)">
                      {{ log.backup_type }}
                    </span>
                  </td>
                  <td>{{ formatDateTime(log.scheduled_time) }}</td>
                  <td>{{ formatDuration(log.start_time, log.end_time) }}</td>
                  <td>{{ formatSize(log.backup_size) }}</td>
                  <td>
                    <span class="badge" :class="getStatusClass(log.status)">
                      {{ log.status.toUpperCase().replace('_', ' ') }}
                    </span>
                  </td>
                  <td>
                    <span v-if="log.is_verified" class="badge bg-success">
                      <i class="bi bi-check-circle me-1"></i>Verified
                    </span>
                    <span v-else class="badge bg-secondary">
                      <i class="bi bi-clock me-1"></i>Pending
                    </span>
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <router-link
                        :to="{ name: 'BackupLogDetail', params: { id: log.id } }"
                        class="btn btn-outline-primary"
                        title="View Details"
                      >
                        <i class="bi bi-eye"></i>
                      </router-link>
                      <router-link
                        :to="{ name: 'EditBackupLog', params: { id: log.id } }"
                        class="btn btn-outline-warning"
                        title="Edit"
                      >
                        <i class="bi bi-pencil"></i>
                      </router-link>
                      <button
                        class="btn btn-outline-danger"
                        @click="confirmDelete(log.id)"
                        title="Delete"
                      >
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="backupLogs.data && backupLogs.data.length === 0">
                  <td colspan="8" class="text-center text-muted py-4">
                    No backup logs found
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="backupLogs.last_page > 1" class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted small">
              Showing {{ backupLogs.from }} to {{ backupLogs.to }} of {{ backupLogs.total }} entries
            </div>
            <nav>
              <ul class="pagination pagination-sm mb-0">
                <li class="page-item" :class="{ disabled: backupLogs.current_page === 1 }">
                  <a class="page-link" href="#" @click.prevent="changePage(backupLogs.current_page - 1)">
                    Previous
                  </a>
                </li>
                <li
                  v-for="page in visiblePages"
                  :key="page"
                  class="page-item"
                  :class="{ active: page === backupLogs.current_page }"
                >
                  <a class="page-link" href="#" @click.prevent="changePage(page)">
                    {{ page }}
                  </a>
                </li>
                <li class="page-item" :class="{ disabled: backupLogs.current_page === backupLogs.last_page }">
                  <a class="page-link" href="#" @click.prevent="changePage(backupLogs.current_page + 1)">
                    Next
                  </a>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import backupLogService from '@/services/backupLogService'
import { confirmAction, notifyError, notifySuccess } from '@/utils/alerts'
import { downloadBlob } from '@/utils/download'

export default {
  name: 'BackupLogList',
  setup() {
    const loading = ref(false)
    const backupLogs = ref({ data: [], current_page: 1, last_page: 1 })
    const systems = ref([])
    const filters = reactive({
      search: '',
      system_id: '',
      backup_type: '',
      status: '',
      is_verified: ''
    })
    const backupTypeOptions = [
      { value: '', label: 'All Types' },
      { value: 'Full', label: 'Full' },
      { value: 'Incremental', label: 'Incremental' },
      { value: 'Differential', label: 'Differential' }
    ]
    const statusOptions = [
      { value: '', label: 'All Status' },
      { value: 'scheduled', label: 'Scheduled' },
      { value: 'in_progress', label: 'In Progress' },
      { value: 'success', label: 'Success' },
      { value: 'failed', label: 'Failed' }
    ]
    const verificationOptions = [
      { value: '', label: 'All Verification' },
      { value: '1', label: 'Verified' },
      { value: '0', label: 'Not Verified' }
    ]
    let searchTimeout = null

    const getBackupTypeClass = (type) => {
      const classes = {
        Full: 'bg-primary',
        Incremental: 'bg-info',
        Differential: 'bg-warning'
      }
      return classes[type] || 'bg-secondary'
    }

    const getStatusClass = (status) => {
      const classes = {
        scheduled: 'bg-secondary',
        in_progress: 'bg-info',
        success: 'bg-success',
        failed: 'bg-danger'
      }
      return classes[status] || 'bg-secondary'
    }

    const formatDateTime = (datetime) => {
      const parsed = new Date(datetime)
      if (Number.isNaN(parsed.getTime())) return datetime
      const pad = (value) => String(value).padStart(2, '0')
      const yyyy = parsed.getFullYear()
      const mm = pad(parsed.getMonth() + 1)
      const dd = pad(parsed.getDate())
      const hh = pad(parsed.getHours())
      const min = pad(parsed.getMinutes())
      const ss = pad(parsed.getSeconds())
      return `${yyyy}/${mm}/${dd} ${hh}:${min}:${ss}`
    }

    const formatDuration = (start, end) => {
      if (!start || !end) return '-'
      const diff = new Date(end) - new Date(start)
      const minutes = Math.floor(diff / 1000 / 60)
      const hours = Math.floor(minutes / 60)
      const mins = minutes % 60
      if (hours > 0) return `${hours}h ${mins}m`
      return `${mins}m`
    }

    const formatSize = (bytes) => {
      if (!bytes) return '-'
      if (bytes < 1024) return bytes + ' B'
      if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB'
      if (bytes < 1024 * 1024 * 1024) return (bytes / 1024 / 1024).toFixed(2) + ' MB'
      return (bytes / 1024 / 1024 / 1024).toFixed(2) + ' GB'
    }

    const visiblePages = computed(() => {
      const current = backupLogs.value.current_page
      const last = backupLogs.value.last_page
      const delta = 2
      const range = []
      const rangeWithDots = []

      for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
        range.push(i)
      }

      if (current - delta > 2) {
        rangeWithDots.push(1, '...')
      } else {
        rangeWithDots.push(1)
      }

      rangeWithDots.push(...range)

      if (current + delta < last - 1) {
        rangeWithDots.push('...', last)
      } else if (last > 1) {
        rangeWithDots.push(last)
      }

      return rangeWithDots.filter(p => p !== '...' || rangeWithDots.indexOf(p) === rangeWithDots.lastIndexOf(p))
    })

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

    const loadBackupLogs = async (page = 1) => {
      loading.value = true
      try {
        const params = buildParams({ page })
        const { data } = await backupLogService.getBackupLogs(params)
        backupLogs.value = data
      } catch (error) {
        console.error('Error loading backup logs:', error)
        notifyError('Failed to load backup logs')
      } finally {
        loading.value = false
      }
    }

    const loadSystems = async () => {
      try {
        const { data } = await backupLogService.getSystems()
        systems.value = data.data
      } catch (error) {
        console.error('Error loading systems:', error)
      }
    }

    const debouncedSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        loadBackupLogs()
      }, 500)
    }

    const changePage = (page) => {
      if (page >= 1 && page <= backupLogs.value.last_page) {
        loadBackupLogs(page)
      }
    }

    const resetFilters = () => {
      filters.search = ''
      filters.system_id = ''
      filters.backup_type = ''
      filters.status = ''
      filters.is_verified = ''
      loadBackupLogs()
    }

    const exportToExcel = async () => {
      try {
        const params = buildParams()
        const response = await backupLogService.exportBackupLogs(params)
        downloadBlob(response.data, 'backup-logs.xlsx')
      } catch (error) {
        notifyError('Failed to export backup logs')
      }
    }

    const confirmDelete = async (id) => {
      const confirmed = await confirmAction('Are you sure you want to delete this backup log?')
      if (!confirmed) return

      try {
        await backupLogService.deleteBackupLog(id)
        notifySuccess('Backup log deleted successfully')
        loadBackupLogs(backupLogs.value.current_page)
      } catch (error) {
        console.error('Error deleting backup log:', error)
        notifyError(error.response?.data?.message || 'Failed to delete backup log')
      }
    }

    onMounted(() => {
      loadSystems()
      loadBackupLogs()
    })

    return {
      loading,
      backupLogs,
      systems,
      filters,
      backupTypeOptions,
      statusOptions,
      verificationOptions,
      visiblePages,
      getBackupTypeClass,
      getStatusClass,
      formatDateTime,
      formatDuration,
      formatSize,
      debouncedSearch,
      changePage,
      resetFilters,
      confirmDelete,
      loadBackupLogs,
      exportToExcel
    }
  }
}
</script>
