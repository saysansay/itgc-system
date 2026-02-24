<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 mb-0">Security Access</h2>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-success" @click="exportToExcel">
          <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
        </button>
        <router-link :to="{ name: 'CreateSecurityAccessRequest' }" class="btn btn-danger">
          <i class="bi bi-plus-circle me-2"></i>Permintaan Baru
        </router-link>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <input
              type="text"
              class="form-control"
              placeholder="Cari no, username, requestor..."
              v-model="filters.search"
              @input="debouncedSearch"
            />
          </div>
          <div class="col-md-4">
            <Select2
              v-model="filters.status"
              :options="statusOptions"
              placeholder="Semua Status"
              @update:modelValue="loadItems"
            />
          </div>
          <div class="col-md-4">
            <Select2
              v-model="filters.department_id"
              :options="departments"
              label-key="name"
              value-key="id"
              placeholder="Semua Departemen"
              @update:modelValue="loadItems"
            />
          </div>
          <div class="col-md-4">
            <Select2
              v-model="filters.requestor_id"
              :options="users"
              label-key="name"
              value-key="id"
              placeholder="Semua Requestor"
              @update:modelValue="loadItems"
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

        <div v-else-if="items.length === 0" class="text-center py-5 text-muted">
          <i class="bi bi-shield-check fs-1 d-block mb-3"></i>
          <p>Tidak ada data</p>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Requestor</th>
                <th>Departemen</th>
                <th>Username</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td>
                  <router-link
                    :to="{ name: 'SecurityAccessRequestDetail', params: { id: item.id } }"
                    class="text-decoration-none"
                  >
                    <code>{{ item.request_number }}</code>
                  </router-link>
                </td>
                <td>{{ formatDateTime(item.requested_at) }}</td>
                <td>{{ item.requestor?.name }}</td>
                <td>{{ item.department?.name }}</td>
                <td>{{ item.username }}</td>
                <td>
                  <span class="badge" :class="getStatusClass(item.status)">
                    {{ formatStatus(item.status) }}
                  </span>
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <router-link
                      :to="{ name: 'SecurityAccessRequestDetail', params: { id: item.id } }"
                      class="btn btn-outline-info"
                      title="View Details"
                    >
                      <i class="bi bi-eye"></i>
                    </router-link>
                    <router-link
                      v-if="item.status === 'pending' || item.status === 'rejected'"
                      :to="{ name: 'EditSecurityAccessRequest', params: { id: item.id } }"
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
import securityAccessService from '@/services/securityAccessService'
import { confirmAction, notifyError, notifySuccess } from '@/utils/alerts'
import { downloadBlob } from '@/utils/download'

export default {
  name: 'SecurityAccessRequestList',
  setup() {
    const loading = ref(false)
    const items = ref([])
    const departments = ref([])
    const users = ref([])
    const pagination = reactive({
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0
    })
    const filters = reactive({
      search: '',
      status: '',
      department_id: '',
      requestor_id: ''
    })

    const statusOptions = [
      { value: '', label: 'Semua Status' },
      { value: 'pending', label: 'Pending' },
      { value: 'approved', label: 'Approved' },
      { value: 'rejected', label: 'Rejected' },
      { value: 'completed', label: 'Completed' }
    ]

    let searchTimeout = null

    const debouncedSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        loadItems()
      }, 400)
    }

    const buildParams = (extra = {}) => {
      const params = {
        page: pagination.current_page,
        per_page: pagination.per_page,
        ...extra
      }
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== '' && value !== null && value !== undefined) {
          params[key] = value
        }
      })
      return params
    }

    const loadItems = async (page = 1) => {
      loading.value = true
      pagination.current_page = page
      try {
        const { data } = await securityAccessService.getSecurityAccessRequests(buildParams())
        items.value = data.data
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.per_page = data.per_page
        pagination.total = data.total
      } catch (error) {
        console.error('Error loading security access:', error)
        notifyError('Gagal memuat data security access')
      } finally {
        loading.value = false
      }
    }

    const loadDepartments = async () => {
      try {
        const { data } = await securityAccessService.getDepartments()
        departments.value = data.data || data
      } catch (error) {
        console.error('Error loading departments:', error)
      }
    }

    const loadUsers = async () => {
      try {
        const { data } = await securityAccessService.getUsers()
        users.value = data.data || data
      } catch (error) {
        console.error('Error loading users:', error)
      }
    }

    const confirmDelete = async (item) => {
      const confirmed = await confirmAction(`Hapus data ${item.request_number}?`)
      if (!confirmed) return

      try {
        await securityAccessService.deleteSecurityAccessRequest(item.id)
        notifySuccess('Data berhasil dihapus')
        loadItems(pagination.current_page)
      } catch (error) {
        console.error('Error deleting item:', error)
        notifyError(error.response?.data?.message || 'Gagal menghapus data')
      }
    }

    const changePage = (page) => {
      if (page < 1 || page > pagination.last_page) return
      loadItems(page)
    }

    const exportToExcel = async () => {
      try {
        const params = buildParams({})
        delete params.page
        delete params.per_page
        const response = await securityAccessService.exportSecurityAccessRequests(params)
        downloadBlob(response.data, 'security-access-requests.xlsx')
      } catch (error) {
        notifyError('Gagal export data')
      }
    }

    const visiblePages = computed(() => {
      const pages = []
      const max = pagination.last_page
      const current = pagination.current_page
      const start = Math.max(1, current - 2)
      const end = Math.min(max, current + 2)
      for (let i = start; i <= end; i += 1) {
        pages.push(i)
      }
      return pages
    })

    const formatStatus = (status) => {
      if (!status) return '-'
      return status.replace('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase())
    }

    const getStatusClass = (status) => {
      const classes = {
        pending: 'bg-warning',
        approved: 'bg-success',
        rejected: 'bg-danger',
        completed: 'bg-secondary'
      }
      return classes[status] || 'bg-secondary'
    }

    const formatDateTime = (datetime) => {
      if (!datetime) return '-'
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

    onMounted(() => {
      loadDepartments()
      loadUsers()
      loadItems()
    })

    return {
      loading,
      items,
      departments,
      users,
      filters,
      pagination,
      statusOptions,
      debouncedSearch,
      loadItems,
      confirmDelete,
      changePage,
      exportToExcel,
      visiblePages,
      formatStatus,
      getStatusClass,
      formatDateTime
    }
  }
}
</script>
