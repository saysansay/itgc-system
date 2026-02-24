<template>
  <div class="container-fluid py-4">
    <div class="mb-4">
      <router-link to="/backup-logs" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left me-2"></i>Back to Backup Logs
      </router-link>
      <h2 class="h4">Backup Log Details</h2>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-danger" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <div v-else-if="backupLog" class="row">
      <div class="col-md-8">
        <div class="card mb-4">
          <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="mb-0">{{ backupLog.system?.name }} - {{ backupLog.backup_type }} Backup</h5>
              <span class="badge" :class="getStatusClass(backupLog.status)">
                {{ backupLog.status.toUpperCase().replace('_', ' ') }}
              </span>
            </div>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="text-muted small">System</label>
                <div class="fw-semibold">{{ backupLog.system?.name }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Backup Type</label>
                <div>
                  <span class="badge" :class="getBackupTypeClass(backupLog.backup_type)">
                    {{ backupLog.backup_type }}
                  </span>
                </div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Scheduled Time</label>
                <div>{{ formatDateTime(backupLog.scheduled_time) }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Status</label>
                <div>
                  <span class="badge" :class="getStatusClass(backupLog.status)">
                    {{ backupLog.status.toUpperCase().replace('_', ' ') }}
                  </span>
                </div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Start Time</label>
                <div>{{ backupLog.start_time ? formatDateTime(backupLog.start_time) : '-' }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">End Time</label>
                <div>{{ backupLog.end_time ? formatDateTime(backupLog.end_time) : '-' }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Duration</label>
                <div>{{ formatDuration(backupLog.start_time, backupLog.end_time) }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Backup Size</label>
                <div>{{ formatSize(backupLog.backup_size) }}</div>
              </div>
              <div class="col-12" v-if="backupLog.backup_location">
                <label class="text-muted small">Backup Location</label>
                <div class="font-monospace small">{{ backupLog.backup_location }}</div>
              </div>
              <div class="col-12" v-if="backupLog.error_message">
                <label class="text-muted small">Error Message</label>
                <div class="alert alert-danger mb-0">{{ backupLog.error_message }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Created At</label>
                <div>{{ formatDateTime(backupLog.created_at) }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Updated At</label>
                <div>{{ formatDateTime(backupLog.updated_at) }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Verification Details -->
        <div class="card">
          <div class="card-header bg-white">
            <h6 class="mb-0">Verification Status</h6>
          </div>
          <div class="card-body">
            <div v-if="backupLog.is_verified">
              <div class="alert alert-success mb-3">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>Backup Verified</strong>
              </div>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="text-muted small">Verified By</label>
                  <div>{{ backupLog.verifier?.name }}</div>
                </div>
                <div class="col-md-6">
                  <label class="text-muted small">Verified At</label>
                  <div>{{ formatDateTime(backupLog.verified_at) }}</div>
                </div>
                <div class="col-12" v-if="backupLog.verification_notes">
                  <label class="text-muted small">Verification Notes</label>
                  <div>{{ backupLog.verification_notes }}</div>
                </div>
              </div>
            </div>
            <div v-else>
              <div class="alert alert-warning mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Verification Pending</strong>
              </div>
              <form @submit.prevent="handleVerify" v-if="backupLog.status === 'success'">
                <div class="mb-3">
                  <label class="form-label">Verification Notes</label>
                  <textarea
                    class="form-control"
                    v-model="verificationForm.verification_notes"
                    rows="3"
                    placeholder="Enter verification details..."
                  ></textarea>
                </div>
                <button type="submit" class="btn btn-success" :disabled="verifying">
                  <span v-if="verifying" class="spinner-border spinner-border-sm me-2"></span>
                  <i class="bi bi-check-circle me-2"></i>Mark as Verified
                </button>
              </form>
              <div v-else class="text-muted">
                <i class="bi bi-info-circle me-2"></i>
                Verification is only available for successful backups
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card">
          <div class="card-header bg-white">
            <h6 class="mb-0">Actions</h6>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <router-link
                :to="{ name: 'EditBackupLog', params: { id: backupLog.id } }"
                class="btn btn-primary"
              >
                <i class="bi bi-pencil me-2"></i>Edit Backup Log
              </router-link>
              <button class="btn btn-danger" @click="confirmDelete">
                <i class="bi bi-trash me-2"></i>Delete Backup Log
              </button>
            </div>
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-header bg-white">
            <h6 class="mb-0">Backup Information</h6>
          </div>
          <div class="card-body">
            <ul class="list-unstyled small mb-0">
              <li class="mb-2">
                <strong>Backup Types:</strong>
                <ul class="mt-1 mb-0">
                  <li><strong>Full:</strong> Complete system backup</li>
                  <li><strong>Incremental:</strong> Changes since last backup</li>
                  <li><strong>Differential:</strong> Changes since last full backup</li>
                </ul>
              </li>
              <li class="mb-2">
                <strong>Best Practices:</strong>
                <ul class="mt-1 mb-0">
                  <li>Verify backup integrity</li>
                  <li>Test restore procedures</li>
                  <li>Store backups securely</li>
                  <li>Document backup process</li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import backupLogService from '@/services/backupLogService'
import { confirmAction, notifyError, notifySuccess } from '@/utils/alerts'

export default {
  name: 'BackupLogDetail',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const loading = ref(false)
    const verifying = ref(false)
    const backupLog = ref(null)
    const verificationForm = reactive({
      verification_notes: ''
    })

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
      if (hours > 0) return `${hours} hours ${mins} minutes`
      return `${mins} minutes`
    }

    const formatSize = (bytes) => {
      if (!bytes) return '-'
      if (bytes < 1024) return bytes + ' B'
      if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB'
      if (bytes < 1024 * 1024 * 1024) return (bytes / 1024 / 1024).toFixed(2) + ' MB'
      return (bytes / 1024 / 1024 / 1024).toFixed(2) + ' GB'
    }

    const loadBackupLog = async () => {
      loading.value = true
      try {
        const { data } = await backupLogService.getBackupLog(route.params.id)
        backupLog.value = data.data
      } catch (error) {
        console.error('Error loading backup log:', error)
        notifyError('Failed to load backup log')
        router.push('/backup-logs')
      } finally {
        loading.value = false
      }
    }

    const handleVerify = async () => {
      verifying.value = true
      try {
        await backupLogService.verifyBackupLog(route.params.id, {
          is_verified: true,
          verification_notes: verificationForm.verification_notes
        })
        notifySuccess('Backup verified successfully')
        loadBackupLog()
      } catch (error) {
        console.error('Error verifying backup:', error)
        notifyError(error.response?.data?.message || 'Failed to verify backup')
      } finally {
        verifying.value = false
      }
    }

    const confirmDelete = async () => {
      const confirmed = await confirmAction('Are you sure you want to delete this backup log?')
      if (!confirmed) return

      try {
        await backupLogService.deleteBackupLog(route.params.id)
        notifySuccess('Backup log deleted successfully')
        router.push('/backup-logs')
      } catch (error) {
        console.error('Error deleting backup log:', error)
        notifyError(error.response?.data?.message || 'Failed to delete backup log')
      }
    }

    onMounted(() => {
      loadBackupLog()
    })

    return {
      loading,
      verifying,
      backupLog,
      verificationForm,
      getBackupTypeClass,
      getStatusClass,
      formatDateTime,
      formatDuration,
      formatSize,
      handleVerify,
      confirmDelete
    }
  }
}
</script>
