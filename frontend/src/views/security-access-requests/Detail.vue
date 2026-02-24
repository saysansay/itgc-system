<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <router-link to="/security-access-requests" class="btn btn-outline-secondary btn-sm mb-3">
          <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar
        </router-link>
        <h2 class="h4 mb-1">Detail Security Access</h2>
        <div class="text-muted">{{ item?.request_number || '-' }}</div>
      </div>
      <div class="d-flex gap-2">
        <button
          v-if="item?.status === 'pending'"
          class="btn btn-success"
          @click="handleApprove"
          :disabled="loadingAction"
        >
          <span v-if="loadingAction" class="spinner-border spinner-border-sm me-2"></span>
          Approve
        </button>
        <button
          v-if="item?.status === 'pending'"
          class="btn btn-danger"
          @click="handleReject"
          :disabled="loadingAction"
        >
          <span v-if="loadingAction" class="spinner-border spinner-border-sm me-2"></span>
          Reject
        </button>
        <button
          v-if="item?.status === 'approved'"
          class="btn btn-secondary"
          @click="handleComplete"
          :disabled="loadingAction"
        >
          <span v-if="loadingAction" class="spinner-border spinner-border-sm me-2"></span>
          Complete
        </button>
        <router-link
          v-if="item?.status === 'pending' || item?.status === 'rejected'"
          :to="{ name: 'EditSecurityAccessRequest', params: { id: item.id } }"
          class="btn btn-outline-primary"
        >
          <i class="bi bi-pencil me-2"></i>Edit
        </router-link>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Tanggal</label>
                <div>{{ formatDateTime(item?.requested_at) }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Requestor</label>
                <div>{{ item?.requestor?.name || '-' }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Departemen</label>
                <div>{{ item?.department?.name || '-' }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">UserName</label>
                <div>{{ item?.username || '-' }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">UserID</label>
                <div>{{ formatAction(item?.user_id_action) }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Password</label>
                <div>{{ formatPassword(item?.password_action) }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <div>{{ formatAction(item?.email_action) }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Internet Access</label>
                <div>{{ formatInternet(item?.internet_access) }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">File Sharing</label>
                <div>{{ formatFileSharing(item?.file_sharing) }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">VPN</label>
                <div>{{ formatAction(item?.vpn_action) }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">ACCPAC</label>
                <div>{{ formatAction(item?.accpac_action) }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">IFS</label>
                <div>{{ formatAction(item?.ifs_action) }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Administrator</label>
                <div>{{ formatAction(item?.administrator_action) }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Fingerprint</label>
                <div>{{ formatAction(item?.fingerprint_action) }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Change Data</label>
                <div>{{ formatAction(item?.change_data_action) }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Restore</label>
                <div>{{ item?.restore ? 'Yes' : 'No' }}</div>
              </div>
              <div class="col-12">
                <label class="form-label">Reason</label>
                <div>{{ item?.reason || '-' }}</div>
              </div>
              <div class="col-12">
                <label class="form-label">Notes</label>
                <div>{{ item?.notes || '-' }}</div>
              </div>
              <div class="col-12" v-if="item?.attachments?.length">
                <label class="form-label">Attachments</label>
                <ul class="list-group">
                  <li
                    v-for="file in item.attachments"
                    :key="file.id"
                    class="list-group-item d-flex justify-content-between align-items-center"
                  >
                    <span class="text-truncate">{{ file.file_name }}</span>
                    <a :href="file.url" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                      View
                    </a>
                  </li>
                </ul>
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
                <span class="badge" :class="getStatusClass(item?.status)">
                  {{ formatStatus(item?.status) }}
                </span>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Approval Date</label>
              <div>{{ item?.approval_date ? formatDateTime(item.approval_date) : '-' }}</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Approved By</label>
              <div>{{ item?.approver?.name || '-' }}</div>
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
import securityAccessService from '@/services/securityAccessService'
import { confirmAction, notifyError, notifySuccess } from '@/utils/alerts'

export default {
  name: 'SecurityAccessRequestDetail',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const item = ref(null)
    const loadingAction = ref(false)

    const loadItem = async () => {
      try {
        const { data } = await securityAccessService.getSecurityAccessRequest(route.params.id)
        item.value = data.data
      } catch (error) {
        console.error('Error loading security access:', error)
        notifyError('Gagal memuat data security access')
        router.push('/security-access-requests')
      }
    }

    const handleApprove = async () => {
      const confirmed = await confirmAction('Approve permintaan ini?')
      if (!confirmed) return

      loadingAction.value = true
      try {
        await securityAccessService.approveSecurityAccessRequest(route.params.id)
        notifySuccess('Permintaan disetujui')
        loadItem()
      } catch (error) {
        console.error('Error approving request:', error)
        notifyError(error.response?.data?.message || 'Gagal approve permintaan')
      } finally {
        loadingAction.value = false
      }
    }

    const handleReject = async () => {
      const confirmed = await confirmAction('Reject permintaan ini?')
      if (!confirmed) return

      loadingAction.value = true
      try {
        await securityAccessService.rejectSecurityAccessRequest(route.params.id)
        notifySuccess('Permintaan ditolak')
        loadItem()
      } catch (error) {
        console.error('Error rejecting request:', error)
        notifyError(error.response?.data?.message || 'Gagal reject permintaan')
      } finally {
        loadingAction.value = false
      }
    }

    const handleComplete = async () => {
      const confirmed = await confirmAction('Tandai permintaan ini sebagai selesai?')
      if (!confirmed) return

      loadingAction.value = true
      try {
        await securityAccessService.completeSecurityAccessRequest(route.params.id)
        notifySuccess('Permintaan diselesaikan')
        loadItem()
      } catch (error) {
        console.error('Error completing request:', error)
        notifyError(error.response?.data?.message || 'Gagal menyelesaikan permintaan')
      } finally {
        loadingAction.value = false
      }
    }

    const formatAction = (value) => {
      const map = {
        new: 'New',
        change: 'Change',
        delete: 'Delete'
      }
      return map[value] || '-'
    }

    const formatPassword = (value) => {
      const map = {
        change: 'Change',
        no_change: 'No Change'
      }
      return map[value] || '-'
    }

    const formatInternet = (value) => {
      const map = {
        control_manager: 'Control Manager',
        control_staff: 'Control Staff'
      }
      return map[value] || '-'
    }

    const formatFileSharing = (value) => {
      const map = {
        full_access: 'Full Access',
        modify: 'Modify',
        read_only: 'ReadOnly'
      }
      return map[value] || '-'
    }

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
      loadItem()
    })

    return {
      item,
      loadingAction,
      formatAction,
      formatPassword,
      formatInternet,
      formatFileSharing,
      formatStatus,
      getStatusClass,
      formatDateTime,
      handleApprove,
      handleReject,
      handleComplete
    }
  }
}
</script>
