<template>
  <div class="container-fluid py-4">
    <div class="mb-4">
      <router-link to="/backup-logs" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left me-2"></i>Back to Backup Logs
      </router-link>
      <h2 class="h4">{{ isEdit ? 'Edit' : 'Create' }} Backup Log</h2>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <form @submit.prevent="handleSubmit">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">System <span class="text-danger">*</span></label>
                  <Select2
                    v-model="formData.system_id"
                    :options="systems"
                    label-key="name"
                    value-key="id"
                    placeholder="Select System"
                    :invalid="!!errors.system_id"
                  />
                  <div v-if="errors.system_id" class="invalid-feedback">
                    {{ errors.system_id[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Backup Type <span class="text-danger">*</span></label>
                  <Select2
                    v-model="formData.backup_type"
                    :options="backupTypeOptions"
                    placeholder="Select Type"
                    :invalid="!!errors.backup_type"
                  />
                  <div v-if="errors.backup_type" class="invalid-feedback">
                    {{ errors.backup_type[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Scheduled Time <span class="text-danger">*</span></label>
                  <FlexDate
                    v-model="formData.scheduled_time"
                    :config="dateTimeConfig"
                    :invalid="!!errors.scheduled_time"
                  />
                  <div v-if="errors.scheduled_time" class="invalid-feedback">
                    {{ errors.scheduled_time[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Status</label>
                  <Select2
                    v-model="formData.status"
                    :options="statusOptions"
                    placeholder="Select Status"
                  />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Start Time</label>
                  <FlexDate
                    v-model="formData.start_time"
                    :config="dateTimeConfig"
                    :invalid="!!errors.start_time"
                  />
                  <div v-if="errors.start_time" class="invalid-feedback">
                    {{ errors.start_time[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">End Time</label>
                  <FlexDate
                    v-model="formData.end_time"
                    :config="dateTimeConfig"
                    :invalid="!!errors.end_time"
                  />
                  <div v-if="errors.end_time" class="invalid-feedback">
                    {{ errors.end_time[0] }}
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label">Backup Location</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="formData.backup_location"
                    :class="{ 'is-invalid': errors.backup_location }"
                    placeholder="/backups/production/db_backup_20240223.bak"
                  />
                  <div v-if="errors.backup_location" class="invalid-feedback">
                    {{ errors.backup_location[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Backup Size (bytes)</label>
                  <input
                    type="number"
                    class="form-control"
                    v-model="formData.backup_size"
                    :class="{ 'is-invalid': errors.backup_size }"
                    min="0"
                    placeholder="0"
                  />
                  <div v-if="errors.backup_size" class="invalid-feedback">
                    {{ errors.backup_size[0] }}
                  </div>
                  <small class="text-muted" v-if="formData.backup_size">
                    {{ formatSize(formData.backup_size) }}
                  </small>
                </div>

                <div class="col-12" v-if="formData.status === 'failed'">
                  <label class="form-label">Error Message</label>
                  <textarea
                    class="form-control"
                    v-model="formData.error_message"
                    :class="{ 'is-invalid': errors.error_message }"
                    rows="3"
                    placeholder="Describe the error or issue encountered..."
                  ></textarea>
                  <div v-if="errors.error_message" class="invalid-feedback">
                    {{ errors.error_message[0] }}
                  </div>
                </div>
              </div>

              <div class="mt-4">
                <button type="submit" class="btn btn-danger me-2" :disabled="submitting">
                  <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                  {{ isEdit ? 'Update' : 'Create' }} Backup Log
                </button>
                <router-link to="/backup-logs" class="btn btn-outline-secondary">
                  Cancel
                </router-link>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card">
          <div class="card-header bg-white">
            <h6 class="mb-0">Backup Guidelines</h6>
          </div>
          <div class="card-body">
            <ul class="small mb-0">
              <li><strong>Full Backup:</strong> Complete system backup</li>
              <li><strong>Incremental:</strong> Changes since last backup</li>
              <li><strong>Differential:</strong> Changes since last full backup</li>
              <li class="mt-2">Schedule backups during off-peak hours</li>
              <li>Verify backup integrity after completion</li>
              <li>Store backups in secure, separate location</li>
              <li>Test restore procedures regularly</li>
              <li>Document backup size and duration</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import backupLogService from '@/services/backupLogService'
import { notifyError, notifySuccess } from '@/utils/alerts'

export default {
  name: 'BackupLogForm',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const submitting = ref(false)
    const systems = ref([])
    const errors = reactive({})
    const formData = reactive({
      system_id: '',
      backup_type: '',
      scheduled_time: '',
      start_time: '',
      end_time: '',
      status: 'scheduled',
      backup_location: '',
      backup_size: null,
      error_message: ''
    })

    const backupTypeOptions = [
      { value: 'Full', label: 'Full Backup' },
      { value: 'Incremental', label: 'Incremental Backup' },
      { value: 'Differential', label: 'Differential Backup' }
    ]

    const statusOptions = [
      { value: 'scheduled', label: 'Scheduled' },
      { value: 'in_progress', label: 'In Progress' },
      { value: 'success', label: 'Success' },
      { value: 'failed', label: 'Failed' }
    ]

    const dateTimeConfig = {
      enableTime: true,
      enableSeconds: true,
      time_24hr: true,
      dateFormat: 'Y-m-d H:i:S'
    }

    const isEdit = computed(() => !!route.params.id)

    const formatSize = (bytes) => {
      if (!bytes) return ''
      if (bytes < 1024) return bytes + ' B'
      if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB'
      if (bytes < 1024 * 1024 * 1024) return (bytes / 1024 / 1024).toFixed(2) + ' MB'
      return (bytes / 1024 / 1024 / 1024).toFixed(2) + ' GB'
    }

    const formatDateTimeLocal = (datetime) => {
      if (!datetime) return ''
      const date = new Date(datetime)
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      const hours = String(date.getHours()).padStart(2, '0')
      const minutes = String(date.getMinutes()).padStart(2, '0')
      const seconds = String(date.getSeconds()).padStart(2, '0')
      return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`
    }

    const loadSystems = async () => {
      try {
        const { data } = await backupLogService.getSystems()
        systems.value = data.data
      } catch (error) {
        console.error('Error loading systems:', error)
      }
    }

    const loadBackupLog = async () => {
      try {
        const { data } = await backupLogService.getBackupLog(route.params.id)
        const log = data.data
        formData.system_id = log.system_id
        formData.backup_type = log.backup_type
        formData.scheduled_time = formatDateTimeLocal(log.scheduled_time)
        formData.start_time = formatDateTimeLocal(log.start_time)
        formData.end_time = formatDateTimeLocal(log.end_time)
        formData.status = log.status
        formData.backup_location = log.backup_location || ''
        formData.backup_size = log.backup_size
        formData.error_message = log.error_message || ''
      } catch (error) {
        console.error('Error loading backup log:', error)
        notifyError('Failed to load backup log')
        router.push('/backup-logs')
      }
    }

    const handleSubmit = async () => {
      submitting.value = true
      Object.keys(errors).forEach(key => delete errors[key])

      try {
        if (isEdit.value) {
          await backupLogService.updateBackupLog(route.params.id, formData)
          notifySuccess('Backup log updated successfully')
        } else {
          await backupLogService.createBackupLog(formData)
          notifySuccess('Backup log created successfully')
        }
        router.push('/backup-logs')
      } catch (error) {
        if (error.response?.data?.errors) {
          Object.assign(errors, error.response.data.errors)
        } else {
          notifyError(error.response?.data?.message || 'Failed to save backup log')
        }
      } finally {
        submitting.value = false
      }
    }

    onMounted(() => {
      loadSystems()
      if (isEdit.value) {
        loadBackupLog()
      }
    })

    return {
      formData,
      errors,
      submitting,
      systems,
      isEdit,
      backupTypeOptions,
      statusOptions,
      dateTimeConfig,
      formatSize,
      handleSubmit
    }
  }
}
</script>
