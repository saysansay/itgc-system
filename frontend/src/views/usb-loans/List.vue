<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 mb-0">Peminjaman USB</h2>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-success" @click="exportToExcel">
          <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
        </button>
        <router-link to="/usb-loans/create" class="btn btn-danger">
          <i class="bi bi-plus-circle me-2"></i>Peminjaman Baru
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
              placeholder="Cari no peminjaman, requestor..."
              v-model="filters.search"
              @input="debouncedSearch"
            />
          </div>
          <div class="col-md-2">
            <Select2
              v-model="filters.status"
              :options="statusOptions"
              placeholder="Semua Status"
              @update:modelValue="loadUsbLoans"
            />
          </div>
          <div class="col-md-2">
            <Select2
              v-model="filters.department_id"
              :options="departments"
              label-key="name"
              value-key="id"
              placeholder="Semua Department"
              @update:modelValue="loadUsbLoans"
            />
          </div>
          <div class="col-md-2">
            <FlexDate
              v-model="filters.start_date"
              :config="dateConfig"
              @update:modelValue="loadUsbLoans"
            />
          </div>
          <div class="col-md-2">
            <FlexDate
              v-model="filters.end_date"
              :config="dateConfig"
              @update:modelValue="loadUsbLoans"
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
                  <th>No. Peminjaman</th>
                  <th>Requestor</th>
                  <th>Department</th>
                  <th>Tanggal Pinjam</th>
                  <th>Tanggal Kembali</th>
                  <th>PIC</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="loan in usbLoans.data" :key="loan.id">
                  <td>
                    <span class="badge bg-dark font-monospace">{{ loan.loan_number }}</span>
                  </td>
                  <td>{{ loan.requestor?.name }}</td>
                  <td>{{ loan.department?.name }}</td>
                  <td>{{ formatDateTime(loan.loan_datetime) }}</td>
                  <td>
                    <div v-if="loan.return_datetime">
                      {{ formatDateTime(loan.return_datetime) }}
                    </div>
                    <div v-else class="text-muted">-</div>
                  </td>
                  <td>{{ loan.pic?.name }}</td>
                  <td>
                    <span class="badge" :class="getStatusClass(loan.status)">
                      {{ getStatusText(loan.status) }}
                    </span>
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <router-link
                        :to="{ name: 'UsbLoanDetail', params: { id: loan.id } }"
                        class="btn btn-outline-primary"
                        title="View Details"
                      >
                        <i class="bi bi-eye"></i>
                      </router-link>
                      <router-link
                        v-if="loan.status === 'pending' || loan.status === 'rejected'"
                        :to="{ name: 'EditUsbLoan', params: { id: loan.id } }"
                        class="btn btn-outline-warning"
                        title="Edit"
                      >
                        <i class="bi bi-pencil"></i>
                      </router-link>
                      <button
                        v-if="loan.status === 'pending' || loan.status === 'rejected'"
                        class="btn btn-outline-danger"
                        @click="confirmDelete(loan.id)"
                        title="Delete"
                      >
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="usbLoans.data && usbLoans.data.length === 0">
                  <td colspan="8" class="text-center text-muted py-4">
                    Tidak ada data peminjaman USB
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="usbLoans.last_page > 1" class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted small">
              Menampilkan {{ usbLoans.from }} sampai {{ usbLoans.to }} dari {{ usbLoans.total }} data
            </div>
            <nav>
              <ul class="pagination pagination-sm mb-0">
                <li class="page-item" :class="{ disabled: usbLoans.current_page === 1 }">
                  <a class="page-link" href="#" @click.prevent="changePage(usbLoans.current_page - 1)">
                    Previous
                  </a>
                </li>
                <li
                  v-for="page in visiblePages"
                  :key="page"
                  class="page-item"
                  :class="{ active: page === usbLoans.current_page }"
                >
                  <a class="page-link" href="#" @click.prevent="changePage(page)">
                    {{ page }}
                  </a>
                </li>
                <li class="page-item" :class="{ disabled: usbLoans.current_page === usbLoans.last_page }">
                  <a class="page-link" href="#" @click.prevent="changePage(usbLoans.current_page + 1)">
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
import usbLoanService from '@/services/usbLoanService'
import { confirmAction, notifyError, notifySuccess } from '@/utils/alerts'
import { downloadBlob } from '@/utils/download'

export default {
  name: 'UsbLoanList',
  setup() {
    const loading = ref(false)
    const usbLoans = ref({ data: [], current_page: 1, last_page: 1 })
    const departments = ref([])
    const filters = reactive({
      search: '',
      status: '',
      department_id: '',
      start_date: '',
      end_date: ''
    })
    const statusOptions = [
      { value: '', label: 'Semua Status' },
      { value: 'pending', label: 'Pending' },
      { value: 'approved', label: 'Approved' },
      { value: 'rejected', label: 'Rejected' },
      { value: 'returned', label: 'Returned' }
    ]
    const dateConfig = {
      dateFormat: 'Y-m-d'
    }
    let searchTimeout = null

    const getStatusClass = (status) => {
      const classes = {
        pending: 'bg-warning',
        approved: 'bg-success',
        rejected: 'bg-danger',
        returned: 'bg-primary'
      }
      return classes[status] || 'bg-secondary'
    }

    const getStatusText = (status) => {
      const texts = {
        pending: 'Pending',
        approved: 'Approved',
        rejected: 'Rejected',
        returned: 'Returned'
      }
      return texts[status] || status
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

    const visiblePages = computed(() => {
      const current = usbLoans.value.current_page
      const last = usbLoans.value.last_page
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

    const loadUsbLoans = async (page = 1) => {
      loading.value = true
      try {
        const params = buildParams({ page })
        const { data } = await usbLoanService.getUsbLoans(params)
        usbLoans.value = data
      } catch (error) {
        console.error('Error loading USB loans:', error)
        notifyError('Gagal memuat data peminjaman USB')
      } finally {
        loading.value = false
      }
    }

    const loadDepartments = async () => {
      try {
        const { data } = await usbLoanService.getDepartments()
        departments.value = data.data || data
      } catch (error) {
        console.error('Error loading departments:', error)
      }
    }

    const debouncedSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        loadUsbLoans()
      }, 500)
    }

    const changePage = (page) => {
      if (page >= 1 && page <= usbLoans.value.last_page) {
        loadUsbLoans(page)
      }
    }

    const resetFilters = () => {
      filters.search = ''
      filters.status = ''
      filters.department_id = ''
      filters.start_date = ''
      filters.end_date = ''
      loadUsbLoans()
    }

    const exportToExcel = async () => {
      try {
        const params = buildParams()
        const response = await usbLoanService.exportUsbLoans(params)
        downloadBlob(response.data, 'usb-loans.xlsx')
      } catch (error) {
        notifyError('Gagal export data peminjaman USB')
      }
    }

    const confirmDelete = async (id) => {
      const confirmed = await confirmAction('Apakah Anda yakin ingin menghapus peminjaman USB ini?')
      if (!confirmed) return

      try {
        await usbLoanService.deleteUsbLoan(id)
        notifySuccess('Peminjaman USB berhasil dihapus')
        loadUsbLoans(usbLoans.value.current_page)
      } catch (error) {
        console.error('Error deleting USB loan:', error)
        notifyError(error.response?.data?.message || 'Gagal menghapus peminjaman USB')
      }
    }

    onMounted(() => {
      loadDepartments()
      loadUsbLoans()
    })

    return {
      loading,
      usbLoans,
      departments,
      filters,
      statusOptions,
      dateConfig,
      visiblePages,
      getStatusClass,
      getStatusText,
      formatDateTime,
      debouncedSearch,
      changePage,
      resetFilters,
      confirmDelete,
      loadUsbLoans,
      exportToExcel
    }
  }
}
</script>
