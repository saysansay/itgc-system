<template>
  <div class="container-fluid py-4">
    <div class="mb-4">
      <router-link to="/usb-loans" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar Peminjaman
      </router-link>
      <h2 class="h4">Detail Peminjaman USB</h2>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-danger" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <div v-else-if="usbLoan" class="row">
      <div class="col-md-8">
        <div class="card mb-4">
          <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="mb-0">{{ usbLoan.loan_number }}</h5>
              <span class="badge" :class="getStatusClass(usbLoan.status)">
                {{ getStatusText(usbLoan.status) }}
              </span>
            </div>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="text-muted small">Requestor</label>
                <div class="fw-semibold">{{ usbLoan.requestor?.name }}</div>
                <div class="small text-muted">{{ usbLoan.requestor?.email }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Department</label>
                <div class="fw-semibold">{{ usbLoan.department?.name }}</div>
              </div>
              <div class="col-12">
                <label class="text-muted small">Tujuan Peminjaman</label>
                <div>{{ usbLoan.purpose }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Tanggal & Jam Peminjaman</label>
                <div>{{ formatDateTime(usbLoan.loan_datetime) }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Rencana Pengembalian</label>
                <div>{{ usbLoan.return_datetime ? formatDateTime(usbLoan.return_datetime) : '-' }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">PIC (IT/Admin)</label>
                <div class="fw-semibold">{{ usbLoan.pic?.name }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Status</label>
                <div>
                  <span class="badge" :class="getStatusClass(usbLoan.status)">
                    {{ getStatusText(usbLoan.status) }}
                  </span>
                </div>
              </div>
              <div class="col-12" v-if="usbLoan.notes">
                <label class="text-muted small">Keterangan</label>
                <div>{{ usbLoan.notes }}</div>
              </div>
              <div class="col-md-6" v-if="usbLoan.approved_by">
                <label class="text-muted small">Disetujui/Ditolak Oleh</label>
                <div>{{ usbLoan.approver?.name }}</div>
              </div>
              <div class="col-md-6" v-if="usbLoan.approved_at">
                <label class="text-muted small">Tanggal Approval</label>
                <div>{{ formatDateTime(usbLoan.approved_at) }}</div>
              </div>
              <div class="col-12" v-if="usbLoan.rejection_reason">
                <label class="text-muted small">Alasan Penolakan</label>
                <div class="alert alert-danger mb-0">{{ usbLoan.rejection_reason }}</div>
              </div>
              <div class="col-md-6" v-if="usbLoan.actual_return_datetime">
                <label class="text-muted small">Tanggal Pengembalian Aktual</label>
                <div>{{ formatDateTime(usbLoan.actual_return_datetime) }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Dibuat</label>
                <div>{{ formatDateTime(usbLoan.created_at) }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Terakhir Diupdate</label>
                <div>{{ formatDateTime(usbLoan.updated_at) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card mb-3">
          <div class="card-header bg-white">
            <h6 class="mb-0">Actions</h6>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <router-link
                v-if="usbLoan.status === 'pending' || usbLoan.status === 'rejected'"
                :to="{ name: 'EditUsbLoan', params: { id: usbLoan.id } }"
                class="btn btn-primary"
              >
                <i class="bi bi-pencil me-2"></i>Edit Peminjaman
              </router-link>
              
              <button
                v-if="usbLoan.status === 'pending'"
                class="btn btn-success"
                @click="showApproveModal = true"
              >
                <i class="bi bi-check-circle me-2"></i>Approve
              </button>
              
              <button
                v-if="usbLoan.status === 'pending'"
                class="btn btn-warning"
                @click="showRejectModal = true"
              >
                <i class="bi bi-x-circle me-2"></i>Reject
              </button>
              
              <button
                v-if="usbLoan.status === 'approved'"
                class="btn btn-info"
                @click="showReturnModal = true"
              >
                <i class="bi bi-box-arrow-left me-2"></i>Kembalikan USB
              </button>
              
              <button
                v-if="usbLoan.status === 'pending' || usbLoan.status === 'rejected'"
                class="btn btn-danger"
                @click="confirmDelete"
              >
                <i class="bi bi-trash me-2"></i>Hapus
              </button>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header bg-white">
            <h6 class="mb-0">Status Peminjaman</h6>
          </div>
          <div class="card-body">
            <ul class="list-unstyled small mb-0">
              <li class="mb-2">
                <span class="badge bg-warning me-2">Pending</span>
                Menunggu persetujuan
              </li>
              <li class="mb-2">
                <span class="badge bg-success me-2">Approved</span>
                Disetujui, USB dapat dipinjam
              </li>
              <li class="mb-2">
                <span class="badge bg-danger me-2">Rejected</span>
                Ditolak
              </li>
              <li class="mb-2">
                <span class="badge bg-primary me-2">Returned</span>
                USB sudah dikembalikan
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Approve Modal -->
    <div v-if="showApproveModal" class="modal d-block" style="background: rgba(0,0,0,0.5)">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Approve Peminjaman USB</h5>
            <button type="button" class="btn-close" @click="showApproveModal = false"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="handleApprove">
              <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                Setujui peminjaman USB untuk <strong>{{ usbLoan?.requestor?.name }}</strong>?
              </div>
              <div class="mb-3">
                <label class="form-label">Catatan (Opsional)</label>
                <textarea
                  class="form-control"
                  v-model="approveForm.notes"
                  rows="3"
                  placeholder="Tambahkan catatan jika diperlukan..."
                ></textarea>
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" @click="showApproveModal = false">
                  Batal
                </button>
                <button type="submit" class="btn btn-success" :disabled="approving">
                  <span v-if="approving" class="spinner-border spinner-border-sm me-2"></span>
                  Approve
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="showRejectModal" class="modal d-block" style="background: rgba(0,0,0,0.5)">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Reject Peminjaman USB</h5>
            <button type="button" class="btn-close" @click="showRejectModal = false"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="handleReject">
              <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Tolak peminjaman USB untuk <strong>{{ usbLoan?.requestor?.name }}</strong>?
              </div>
              <div class="mb-3">
                <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                <textarea
                  class="form-control"
                  v-model="rejectForm.rejection_reason"
                  rows="3"
                  placeholder="Jelaskan alasan penolakan..."
                  required
                ></textarea>
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" @click="showRejectModal = false">
                  Batal
                </button>
                <button type="submit" class="btn btn-warning" :disabled="rejecting">
                  <span v-if="rejecting" class="spinner-border spinner-border-sm me-2"></span>
                  Reject
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Return Modal -->
    <div v-if="showReturnModal" class="modal d-block" style="background: rgba(0,0,0,0.5)">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Kembalikan USB</h5>
            <button type="button" class="btn-close" @click="showReturnModal = false"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="handleReturn">
              <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Konfirmasi pengembalian USB dari <strong>{{ usbLoan?.requestor?.name }}</strong>
              </div>
              <div class="mb-3">
                <label class="form-label">Tanggal & Jam Pengembalian <span class="text-danger">*</span></label>
                <FlexDate
                  v-model="returnForm.actual_return_datetime"
                  :config="dateTimeConfig"
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Catatan (Opsional)</label>
                <textarea
                  class="form-control"
                  v-model="returnForm.notes"
                  rows="3"
                  placeholder="Kondisi USB saat dikembalikan..."
                ></textarea>
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" @click="showReturnModal = false">
                  Batal
                </button>
                <button type="submit" class="btn btn-info" :disabled="returning">
                  <span v-if="returning" class="spinner-border spinner-border-sm me-2"></span>
                  Konfirmasi Pengembalian
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import usbLoanService from '@/services/usbLoanService'
import { confirmAction, notifyError, notifySuccess } from '@/utils/alerts'

export default {
  name: 'UsbLoanDetail',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const loading = ref(false)
    const approving = ref(false)
    const rejecting = ref(false)
    const returning = ref(false)
    const usbLoan = ref(null)
    const showApproveModal = ref(false)
    const showRejectModal = ref(false)
    const showReturnModal = ref(false)

    const approveForm = reactive({
      notes: ''
    })

    const rejectForm = reactive({
      rejection_reason: ''
    })

    const returnForm = reactive({
      actual_return_datetime: new Date().toISOString().slice(0, 16),
      notes: ''
    })

    const dateTimeConfig = {
      enableTime: true,
      enableSeconds: true,
      time_24hr: true,
      dateFormat: 'Y-m-d H:i:S'
    }

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

    const loadUsbLoan = async () => {
      loading.value = true
      try {
        const { data } = await usbLoanService.getUsbLoan(route.params.id)
        usbLoan.value = data.data
      } catch (error) {
        console.error('Error loading USB loan:', error)
        notifyError('Gagal memuat data peminjaman USB')
        router.push('/usb-loans')
      } finally {
        loading.value = false
      }
    }

    const handleApprove = async () => {
      approving.value = true
      try {
        await usbLoanService.approveUsbLoan(route.params.id, approveForm)
        notifySuccess('Peminjaman USB berhasil diapprove')
        showApproveModal.value = false
        loadUsbLoan()
      } catch (error) {
        console.error('Error approving USB loan:', error)
        notifyError(error.response?.data?.message || 'Gagal approve peminjaman USB')
      } finally {
        approving.value = false
      }
    }

    const handleReject = async () => {
      rejecting.value = true
      try {
        await usbLoanService.rejectUsbLoan(route.params.id, rejectForm)
        notifySuccess('Peminjaman USB berhasil direject')
        showRejectModal.value = false
        loadUsbLoan()
      } catch (error) {
        console.error('Error rejecting USB loan:', error)
        notifyError(error.response?.data?.message || 'Gagal reject peminjaman USB')
      } finally {
        rejecting.value = false
      }
    }

    const handleReturn = async () => {
      returning.value = true
      try {
        await usbLoanService.returnUsbLoan(route.params.id, returnForm)
        notifySuccess('USB berhasil dikembalikan')
        showReturnModal.value = false
        loadUsbLoan()
      } catch (error) {
        console.error('Error returning USB:', error)
        notifyError(error.response?.data?.message || 'Gagal mengembalikan USB')
      } finally {
        returning.value = false
      }
    }

    const confirmDelete = async () => {
      const confirmed = await confirmAction('Apakah Anda yakin ingin menghapus peminjaman USB ini?')
      if (!confirmed) return

      try {
        await usbLoanService.deleteUsbLoan(route.params.id)
        notifySuccess('Peminjaman USB berhasil dihapus')
        router.push('/usb-loans')
      } catch (error) {
        console.error('Error deleting USB loan:', error)
        notifyError(error.response?.data?.message || 'Gagal menghapus peminjaman USB')
      }
    }

    onMounted(() => {
      loadUsbLoan()
    })

    return {
      loading,
      approving,
      rejecting,
      returning,
      usbLoan,
      showApproveModal,
      showRejectModal,
      showReturnModal,
      approveForm,
      rejectForm,
      returnForm,
      dateTimeConfig,
      getStatusClass,
      getStatusText,
      formatDateTime,
      handleApprove,
      handleReject,
      handleReturn,
      confirmDelete
    }
  }
}
</script>
