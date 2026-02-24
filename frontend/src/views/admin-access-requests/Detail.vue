<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <router-link to="/admin-access-requests" class="btn btn-outline-secondary btn-sm mb-3">
          <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar
        </router-link>
        <h2 class="h4 mb-1">Detail Peminjaman Akses Administrator</h2>
        <div class="text-muted">{{ requestItem?.request_number || '-' }}</div>
      </div>
      <div class="d-flex gap-2">
        <button
          v-if="requestItem?.status === 'pending'"
          class="btn btn-success"
          @click="handleApprove"
          :disabled="loadingAction"
        >
          <span v-if="loadingAction" class="spinner-border spinner-border-sm me-2"></span>
          Approve
        </button>
        <button
          v-if="requestItem?.status === 'pending'"
          class="btn btn-danger"
          @click="handleReject"
          :disabled="loadingAction"
        >
          <span v-if="loadingAction" class="spinner-border spinner-border-sm me-2"></span>
          Reject
        </button>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Requestor</label>
                <div>{{ requestItem?.requestor?.name || '-' }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Departemen</label>
                <div>{{ requestItem?.department?.name || '-' }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Request Type</label>
                <div>{{ formatRequestType(requestItem?.request_type) }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Durasi</label>
                <div>{{ formatDuration(requestItem?.duration_value, requestItem?.duration_unit) }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Metode</label>
                <div>{{ formatMethod(requestItem?.method) }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Hostname</label>
                <div>{{ requestItem?.hostname || '-' }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">UserAdministrator</label>
                <div>{{ requestItem?.user_administrator || '-' }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Partner</label>
                <div>{{ requestItem?.partner || '-' }}</div>
              </div>
              <div class="col-12">
                <label class="form-label">Tujuan</label>
                <div>{{ requestItem?.purpose || '-' }}</div>
              </div>
              <div class="col-12">
                <label class="form-label">Keterangan</label>
                <div>{{ requestItem?.notes || '-' }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card">
          <div class="card-header bg-white">
            <h6 class="mb-0">Status</h6>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">Status</label>
              <div>
                <span class="badge" :class="getStatusClass(requestItem?.status)">
                  {{ formatStatus(requestItem?.status) }}
                </span>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Tanggal Request</label>
              <div>{{ formatDateTime(requestItem?.requested_at) }}</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Tanggal Approval</label>
              <div>{{ requestItem?.approved_at ? formatDateTime(requestItem.approved_at) : '-' }}</div>
            </div>
            <div class="mb-3">
              <label class="form-label">DiApprove Oleh</label>
              <div>{{ requestItem?.approver?.name || '-' }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import adminAccessRequestService from '@/services/adminAccessRequestService'
import { confirmAction, notifyError, notifySuccess } from '@/utils/alerts'

export default {
  name: 'AdminAccessRequestDetail',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const requestItem = ref(null)
    const loadingAction = ref(false)

    const formatRequestType = (type) => {
      const map = {
        temporary: 'Temporary',
        permanent: 'Permanent',
        emergency: 'Emergency',
        maintenance: 'Maintenance'
      }
      return map[type] || '-'
    }

    const formatMethod = (method) => {
      const map = {
        vpn: 'VPN',
        rdp: 'RDP',
        local: 'Local',
        server_console: 'Server Console',
        others: 'Others'
      }
      return map[method] || '-'
    }

    const formatDuration = (value, unit) => {
      if (!value || !unit) return '-'
      const unitLabel = unit === 'hour' ? 'Jam' : 'Hari'
      return `${value} ${unitLabel}`
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

    const formatStatus = (status) => {
      if (!status) return '-'
      return status.replace(/\b\w/g, (char) => char.toUpperCase())
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

    const loadRequest = async () => {
      try {
        const { data } = await adminAccessRequestService.getAdminAccessRequest(route.params.id)
        requestItem.value = data.data
      } catch (error) {
        console.error('Error loading request:', error)
        notifyError('Gagal memuat data permintaan')
        router.push('/admin-access-requests')
      }
    }

    const handleApprove = async () => {
      const confirmed = await confirmAction('Setujui permintaan ini?')
      if (!confirmed) return

      loadingAction.value = true
      try {
        await adminAccessRequestService.approveAdminAccessRequest(route.params.id, {})
        notifySuccess('Permintaan disetujui')
        loadRequest()
      } catch (error) {
        console.error('Error approving request:', error)
        notifyError(error.response?.data?.message || 'Gagal approve permintaan')
      } finally {
        loadingAction.value = false
      }
    }

    const handleReject = async () => {
      const confirmed = await confirmAction('Tolak permintaan ini?')
      if (!confirmed) return

      loadingAction.value = true
      try {
        await adminAccessRequestService.rejectAdminAccessRequest(route.params.id, {})
        notifySuccess('Permintaan ditolak')
        loadRequest()
      } catch (error) {
        console.error('Error rejecting request:', error)
        notifyError(error.response?.data?.message || 'Gagal reject permintaan')
      } finally {
        loadingAction.value = false
      }
    }

    onMounted(() => {
      loadRequest()
    })

    return {
      requestItem,
      loadingAction,
      formatRequestType,
      formatMethod,
      formatDuration,
      getStatusClass,
      formatStatus,
      formatDateTime,
      handleApprove,
      handleReject
    }
  }
}
</script>
