<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 mb-0">Peminjaman Akses Administrator</h2>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-success" @click="exportToExcel">
          <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
        </button>
        <router-link :to="{ name: 'CreateAdminAccessRequest' }" class="btn btn-danger">
          <i class="bi bi-plus-circle me-2"></i>Permintaan Baru
        </router-link>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <input
              type="text"
              class="form-control"
              placeholder="Cari no, hostname, requestor..."
              v-model="filters.search"
              @input="debouncedSearch"
            />
          </div>
          <div class="col-md-3">
            <Select2
              v-model="filters.request_type"
              :options="requestTypeOptions"
              placeholder="Semua Tipe"
              @update:modelValue="loadRequests"
            />
          </div>
          <div class="col-md-3">
            <Select2
              v-model="filters.method"
              :options="methodOptions"
              placeholder="Semua Metode"
              @update:modelValue="loadRequests"
            />
          </div>
          <div class="col-md-3">
            <Select2
              v-model="filters.status"
              :options="statusOptions"
              placeholder="Semua Status"
              @update:modelValue="loadRequests"
            />
          </div>
          <div class="col-md-3">
            <Select2
              v-model="filters.department_id"
              :options="departments"
              label-key="name"
              value-key="id"
              placeholder="Semua Departemen"
              @update:modelValue="loadRequests"
            />
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-danger" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>

        <div v-else-if="requests.length === 0" class="text-center py-5 text-muted">
          <i class="bi bi-shield-lock fs-1 d-block mb-3"></i>
          <p>Tidak ada data</p>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>No</th>
                <th>Requestor</th>
                <th>Departemen</th>
                <th>Tipe</th>
                <th>Metode</th>
                <th>Durasi</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in requests" :key="item.id">
                <td>
                  <router-link
                    :to="{ name: 'AdminAccessRequestDetail', params: { id: item.id } }"
                    class="text-decoration-none"
                  >
                    <code>{{ item.request_number }}</code>
                  </router-link>
                </td>
                <td>{{ item.requestor?.name }}</td>
                <td>{{ item.department?.name }}</td>
                <td>{{ formatRequestType(item.request_type) }}</td>
                <td>{{ formatMethod(item.method) }}</td>
                <td>{{ formatDuration(item.duration_value, item.duration_unit) }}</td>
                <td>
                  <span class="badge" :class="getStatusClass(item.status)">
                    {{ formatStatus(item.status) }}
                  </span>
                </td>
                <td>{{ formatDateTime(item.requested_at) }}</td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <router-link
                      :to="{ name: 'AdminAccessRequestDetail', params: { id: item.id } }"
                      class="btn btn-outline-info"
                      title="View Details"
                    >
                      <i class="bi bi-eye"></i>
                    </router-link>
                    <router-link
                      v-if="item.status === 'pending' || item.status === 'rejected'"
                      :to="{ name: 'EditAdminAccessRequest', params: { id: item.id } }"
                      class="btn btn-outline-primary"
                      title="Edit"
                    >
                      <i class="bi bi-pencil"></i>
                    </router-link>
                    <button
                      v-if="item.status === 'pending' || item.status === 'rejected'"
                      class="btn btn-outline-danger"
                      @click="confirmDelete(item)"
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
import adminAccessRequestService from '@/services/adminAccessRequestService'
import { confirmAction, notifyError, notifySuccess } from '@/utils/alerts'
import { downloadBlob } from '@/utils/download'

export default {
  name: 'AdminAccessRequestList',
  setup() {
    const router = useRouter()
    const loading = ref(false)
    const requests = ref([])
    const departments = ref([])
    const pagination = reactive({
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0
    })
    const filters = reactive({
      search: '',
      request_type: '',
      method: '',
      status: '',
      department_id: ''
    })

    const requestTypeOptions = [
      { value: '', label: 'Semua Tipe' },
      { value: 'temporary', label: 'Temporary' },
      { value: 'permanent', label: 'Permanent' },
      { value: 'emergency', label: 'Emergency' },
      { value: 'maintenance', label: 'Maintenance' }
    ]

    const methodOptions = [
      { value: '', label: 'Semua Metode' },
      { value: 'vpn', label: 'VPN' },
      { value: 'rdp', label: 'RDP' },
      { value: 'local', label: 'Local' },
      { value: 'server_console', label: 'Server Console' },
      { value: 'others', label: 'Others' }
    ]

    const statusOptions = [
      { value: '', label: 'Semua Status' },
      { value: 'pending', label: 'Pending' },
      { value: 'approved', label: 'Approved' },
      { value: 'rejected', label: 'Rejected' },
      { value: 'expired', label: 'Expired' }
    ]

    let searchTimeout = null

    const debouncedSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        loadRequests()
      }, 500)
    }

    const formatRequestType = (type) => {
      const map = {
        temporary: 'Temporary',
        permanent: 'Permanent',
        emergency: 'Emergency',
        maintenance: 'Maintenance'
      }
      return map[type] || type
    }

    const formatMethod = (method) => {
      const map = {
        vpn: 'VPN',
        rdp: 'RDP',
        local: 'Local',
        server_console: 'Server Console',
        others: 'Others'
      }
      return map[method] || method
    }

    const formatDuration = (value, unit) => {
      if (!value || !unit) return '-'
      const unitLabel = unit === 'hour' ? 'Jam' : 'Hari'
      return `${value} ${unitLabel}`
    }

    const formatStatus = (status) => {
      return status.replace(/\b\w/g, (char) => char.toUpperCase())
    }

    const getStatusClass = (status) => {
      const classes = {
        pending: 'bg-warning',
        approved: 'bg-success',
        rejected: 'bg-danger',
        expired: 'bg-secondary'
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

    const loadDepartments = async () => {
      try {
        const { data } = await adminAccessRequestService.getDepartments()
        departments.value = data.data || data
      } catch (error) {
        console.error('Error loading departments:', error)
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

    const loadRequests = async () => {
      loading.value = true
      try {
        const params = buildParams({
          page: pagination.current_page,
          per_page: pagination.per_page
        })
        const { data } = await adminAccessRequestService.getAdminAccessRequests(params)
        requests.value = data.data
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.total = data.total
      } catch (error) {
        console.error('Error loading admin access requests:', error)
        notifyError('Gagal memuat data')
      } finally {
        loading.value = false
      }
    }

    const changePage = (page) => {
      if (page >= 1 && page <= pagination.last_page) {
        pagination.current_page = page
        loadRequests()
      }
    }

    const confirmDelete = async (item) => {
      const confirmed = await confirmAction(`Hapus permintaan ${item.request_number}?`)
      if (!confirmed) return

      try {
        await adminAccessRequestService.deleteAdminAccessRequest(item.id)
        notifySuccess('Permintaan berhasil dihapus')
        loadRequests()
      } catch (error) {
        console.error('Error deleting request:', error)
        notifyError(error.response?.data?.message || 'Gagal menghapus permintaan')
      }
    }

    const exportToExcel = async () => {
      try {
        const params = buildParams()
        const response = await adminAccessRequestService.exportAdminAccessRequests(params)
        downloadBlob(response.data, 'admin-access-requests.xlsx')
      } catch (error) {
        notifyError('Gagal export data')
      }
    }

    const visiblePages = computed(() => {
      const pages = []
      const current = pagination.current_page
      const last = pagination.last_page

      if (last <= 7) {
        for (let i = 1; i <= last; i++) pages.push(i)
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
      loadRequests()
    })

    return {
      loading,
      requests,
      departments,
      pagination,
      filters,
      requestTypeOptions,
      methodOptions,
      statusOptions,
      debouncedSearch,
      formatRequestType,
      formatMethod,
      formatDuration,
      formatStatus,
      getStatusClass,
      formatDateTime,
      loadRequests,
      changePage,
      confirmDelete,
      exportToExcel,
      visiblePages
    }
  }
}
</script>
