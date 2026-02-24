<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 mb-0">General Trouble</h2>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-success" @click="exportToExcel">
          <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
        </button>
        <router-link :to="{ name: 'CreateGeneralTrouble' }" class="btn btn-danger">
          <i class="bi bi-plus-circle me-2"></i>Tambah Data
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
              placeholder="Cari no, problem, user..."
              v-model="filters.search"
              @input="debouncedSearch"
            />
          </div>
          <div class="col-md-3">
            <Select2
              v-model="filters.type"
              :options="typeOptions"
              placeholder="Semua Tipe"
              @update:modelValue="loadItems"
            />
          </div>
          <div class="col-md-3">
            <Select2
              v-model="filters.status"
              :options="statusOptions"
              placeholder="Semua Status"
              @update:modelValue="loadItems"
            />
          </div>
          <div class="col-md-3">
            <Select2
              v-model="filters.user_id"
              :options="users"
              label-key="name"
              value-key="id"
              placeholder="Semua User"
              @update:modelValue="loadItems"
            />
          </div>
          <div class="col-md-3">
            <Select2
              v-model="filters.pic_id"
              :options="itUsers"
              label-key="name"
              value-key="id"
              placeholder="Semua PIC"
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
          <i class="bi bi-exclamation-triangle fs-1 d-block mb-3"></i>
          <p>Tidak ada data</p>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>User</th>
                <th>PIC</th>
                <th>Tipe</th>
                <th>Durasi</th>
                <th>Status</th>
                <th>Partner</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td>
                  <router-link
                    :to="{ name: 'GeneralTroubleDetail', params: { id: item.id } }"
                    class="text-decoration-none"
                  >
                    <code>{{ item.trouble_number }}</code>
                  </router-link>
                </td>
                <td>{{ formatDateTime(item.reported_at) }}</td>
                <td>{{ item.user?.name }}</td>
                <td>{{ item.pic?.name }}</td>
                <td>{{ formatType(item.type) }}</td>
                <td>{{ formatDuration(item.duration_value, item.duration_unit) }}</td>
                <td>
                  <span class="badge" :class="getStatusClass(item.status)">
                    {{ formatStatus(item.status) }}
                  </span>
                </td>
                <td>{{ item.partner || '-' }}</td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <router-link
                      :to="{ name: 'GeneralTroubleDetail', params: { id: item.id } }"
                      class="btn btn-outline-info"
                      title="View Details"
                    >
                      <i class="bi bi-eye"></i>
                    </router-link>
                    <router-link
                      :to="{ name: 'EditGeneralTrouble', params: { id: item.id } }"
                      class="btn btn-outline-primary"
                      title="Edit"
                    >
                      <i class="bi bi-pencil"></i>
                    </router-link>
                    <button
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
import generalTroubleService from '@/services/generalTroubleService'
import { confirmAction, notifyError, notifySuccess } from '@/utils/alerts'
import { downloadBlob } from '@/utils/download'

export default {
  name: 'GeneralTroubleList',
  setup() {
    const router = useRouter()
    const loading = ref(false)
    const items = ref([])
    const users = ref([])
    const itUsers = ref([])
    const pagination = reactive({
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0
    })
    const filters = reactive({
      search: '',
      type: '',
      status: '',
      user_id: '',
      pic_id: ''
    })

    const typeOptions = [
      { value: '', label: 'Semua Tipe' },
      { value: 'hardware', label: 'Hardware' },
      { value: 'software', label: 'Software' },
      { value: 'network', label: 'Network' },
      { value: 'security', label: 'Security' },
      { value: 'others', label: 'Others' }
    ]

    const statusOptions = [
      { value: '', label: 'Semua Status' },
      { value: 'open', label: 'Open' },
      { value: 'on_progress', label: 'On Progress' },
      { value: 'done', label: 'Done' },
      { value: 'closed', label: 'Closed' }
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
        const { data } = await generalTroubleService.getGeneralTroubles(buildParams())
        items.value = data.data
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.per_page = data.per_page
        pagination.total = data.total
      } catch (error) {
        console.error('Error loading general troubles:', error)
        notifyError('Gagal memuat data general trouble')
      } finally {
        loading.value = false
      }
    }

    const loadUsers = async () => {
      try {
        const { data } = await generalTroubleService.getUsers()
        users.value = data.data || data
      } catch (error) {
        console.error('Error loading users:', error)
      }
    }

    const loadItUsers = async () => {
      try {
        const { data } = await generalTroubleService.getItAdminUsers()
        itUsers.value = data.data || []
      } catch (error) {
        console.error('Error loading IT users:', error)
      }
    }

    const confirmDelete = async (item) => {
      const confirmed = await confirmAction(`Hapus data ${item.trouble_number}?`)
      if (!confirmed) return

      try {
        await generalTroubleService.deleteGeneralTrouble(item.id)
        notifySuccess('Data berhasil dihapus')
        loadItems(pagination.current_page)
      } catch (error) {
        console.error('Error deleting item:', error)
        notifyError(error.response?.data?.message || 'Gagal menghapus data')
      }
    }

    const exportToExcel = async () => {
      try {
        const params = buildParams({})
        delete params.page
        delete params.per_page
        const response = await generalTroubleService.exportGeneralTroubles(params)
        downloadBlob(response.data, 'general-troubles.xlsx')
      } catch (error) {
        notifyError('Gagal export data')
      }
    }

    const changePage = (page) => {
      if (page < 1 || page > pagination.last_page) return
      loadItems(page)
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

    const formatType = (value) => {
      const map = {
        hardware: 'Hardware',
        software: 'Software',
        network: 'Network',
        security: 'Security',
        others: 'Others'
      }
      return map[value] || '-'
    }

    const formatStatus = (status) => {
      if (!status) return '-'
      return status.replace('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase())
    }

    const getStatusClass = (status) => {
      const classes = {
        open: 'bg-warning',
        on_progress: 'bg-primary',
        done: 'bg-success',
        closed: 'bg-secondary'
      }
      return classes[status] || 'bg-secondary'
    }

    const formatDuration = (value, unit) => {
      if (!value || !unit) return '-'
      const unitLabel = unit === 'minute' ? 'Menit' : 'Jam'
      return `${value} ${unitLabel}`
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
      loadUsers()
      loadItUsers()
      loadItems()
    })

    return {
      router,
      loading,
      items,
      users,
      itUsers,
      filters,
      pagination,
      typeOptions,
      statusOptions,
      debouncedSearch,
      loadItems,
      confirmDelete,
      exportToExcel,
      changePage,
      visiblePages,
      formatType,
      formatStatus,
      getStatusClass,
      formatDuration,
      formatDateTime
    }
  }
}
</script>
