<template>
  <div class="container-fluid py-4">
    <div class="mb-4">
      <router-link to="/security-access-requests" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar
      </router-link>
      <h2 class="h4">{{ isEdit ? 'Edit' : 'Buat' }} Security Access</h2>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-body">
            <form @submit.prevent="handleSubmit">
              <div class="row g-3">
                <div class="col-12" v-if="isEdit">
                  <label class="form-label">No</label>
                  <input type="text" class="form-control" :value="formData.request_number" disabled />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Tanggal (Request)</label>
                  <input type="text" class="form-control" :value="requestedAtDisplay" disabled />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Requestor <span class="text-danger">*</span></label>
                  <Select2
                    v-model="formData.requestor_id"
                    :options="users"
                    label-key="name"
                    value-key="id"
                    placeholder="Pilih Requestor"
                    :invalid="!!errors.requestor_id"
                  />
                  <div v-if="errors.requestor_id" class="invalid-feedback">
                    {{ errors.requestor_id[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Departemen <span class="text-danger">*</span></label>
                  <Select2
                    v-model="formData.department_id"
                    :options="departments"
                    label-key="name"
                    value-key="id"
                    placeholder="Pilih Departemen"
                    :invalid="!!errors.department_id"
                  />
                  <div v-if="errors.department_id" class="invalid-feedback">
                    {{ errors.department_id[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">UserName <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="formData.username"
                    :class="{ 'is-invalid': errors.username }"
                    placeholder="Username"
                  />
                  <div v-if="errors.username" class="invalid-feedback">
                    {{ errors.username[0] }}
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">UserID</label>
                  <Select2
                    v-model="formData.user_id_action"
                    :options="actionOptions"
                    placeholder="Pilih"
                    :invalid="!!errors.user_id_action"
                  />
                  <div v-if="errors.user_id_action" class="invalid-feedback">
                    {{ errors.user_id_action[0] }}
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Password</label>
                  <Select2
                    v-model="formData.password_action"
                    :options="passwordOptions"
                    placeholder="Pilih"
                    :invalid="!!errors.password_action"
                  />
                  <div v-if="errors.password_action" class="invalid-feedback">
                    {{ errors.password_action[0] }}
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Email</label>
                  <Select2
                    v-model="formData.email_action"
                    :options="actionOptions"
                    placeholder="Pilih"
                    :invalid="!!errors.email_action"
                  />
                  <div v-if="errors.email_action" class="invalid-feedback">
                    {{ errors.email_action[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Internet Access</label>
                  <Select2
                    v-model="formData.internet_access"
                    :options="internetOptions"
                    placeholder="Pilih"
                    :invalid="!!errors.internet_access"
                  />
                  <div v-if="errors.internet_access" class="invalid-feedback">
                    {{ errors.internet_access[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">File Sharing</label>
                  <Select2
                    v-model="formData.file_sharing"
                    :options="fileSharingOptions"
                    placeholder="Pilih"
                    :invalid="!!errors.file_sharing"
                  />
                  <div v-if="errors.file_sharing" class="invalid-feedback">
                    {{ errors.file_sharing[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">VPN</label>
                  <Select2
                    v-model="formData.vpn_action"
                    :options="actionOptions"
                    placeholder="Pilih"
                    :invalid="!!errors.vpn_action"
                  />
                  <div v-if="errors.vpn_action" class="invalid-feedback">
                    {{ errors.vpn_action[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">ACCPAC</label>
                  <Select2
                    v-model="formData.accpac_action"
                    :options="actionOptions"
                    placeholder="Pilih"
                    :invalid="!!errors.accpac_action"
                  />
                  <div v-if="errors.accpac_action" class="invalid-feedback">
                    {{ errors.accpac_action[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">IFS</label>
                  <Select2
                    v-model="formData.ifs_action"
                    :options="actionOptions"
                    placeholder="Pilih"
                    :invalid="!!errors.ifs_action"
                  />
                  <div v-if="errors.ifs_action" class="invalid-feedback">
                    {{ errors.ifs_action[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Administrator</label>
                  <Select2
                    v-model="formData.administrator_action"
                    :options="actionOptions"
                    placeholder="Pilih"
                    :invalid="!!errors.administrator_action"
                  />
                  <div v-if="errors.administrator_action" class="invalid-feedback">
                    {{ errors.administrator_action[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Fingerprint</label>
                  <Select2
                    v-model="formData.fingerprint_action"
                    :options="actionOptions"
                    placeholder="Pilih"
                    :invalid="!!errors.fingerprint_action"
                  />
                  <div v-if="errors.fingerprint_action" class="invalid-feedback">
                    {{ errors.fingerprint_action[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Change Data</label>
                  <Select2
                    v-model="formData.change_data_action"
                    :options="actionOptions"
                    placeholder="Pilih"
                    :invalid="!!errors.change_data_action"
                  />
                  <div v-if="errors.change_data_action" class="invalid-feedback">
                    {{ errors.change_data_action[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Restore</label>
                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      id="restore"
                      v-model="formData.restore"
                    />
                    <label class="form-check-label" for="restore">Restore</label>
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label">Reason</label>
                  <textarea
                    class="form-control"
                    v-model="formData.reason"
                    :class="{ 'is-invalid': errors.reason }"
                    rows="3"
                    placeholder="Alasan permintaan"
                  ></textarea>
                  <div v-if="errors.reason" class="invalid-feedback">
                    {{ errors.reason[0] }}
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label">Notes</label>
                  <textarea
                    class="form-control"
                    v-model="formData.notes"
                    :class="{ 'is-invalid': errors.notes }"
                    rows="3"
                    placeholder="Catatan tambahan"
                  ></textarea>
                  <div v-if="errors.notes" class="invalid-feedback">
                    {{ errors.notes[0] }}
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label">Attach File (PDF/JPG/PNG)</label>
                  <input
                    type="file"
                    class="form-control"
                    multiple
                    @change="handleFileChange"
                  />
                  <small class="text-muted">Maks 5MB per file.</small>
                </div>

                <div v-if="attachments.length" class="col-12">
                  <label class="form-label">Lampiran Saat Ini</label>
                  <ul class="list-group">
                    <li
                      v-for="file in attachments"
                      :key="file.id"
                      class="list-group-item d-flex justify-content-between align-items-center"
                    >
                      <span class="text-truncate">{{ file.file_name }}</span>
                      <div class="d-flex gap-2">
                        <a :href="file.url" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                          View
                        </a>
                        <button
                          type="button"
                          class="btn btn-sm btn-outline-danger"
                          @click="handleRemoveAttachment(file)"
                        >
                          Delete
                        </button>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>

              <div class="mt-4">
                <button type="submit" class="btn btn-danger me-2" :disabled="submitting">
                  <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                  {{ isEdit ? 'Update' : 'Buat' }} Permintaan
                </button>
                <router-link to="/security-access-requests" class="btn btn-outline-secondary">
                  Batal
                </router-link>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card">
          <div class="card-header bg-white">
            <h6 class="mb-0">Panduan</h6>
          </div>
          <div class="card-body">
            <ul class="small mb-0">
              <li><strong>Status:</strong> Pending, Approved, Rejected, Completed</li>
              <li><strong>Restore:</strong> Centang jika diperlukan</li>
              <li><strong>Attachments:</strong> PDF/JPG/PNG</li>
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
import securityAccessService from '@/services/securityAccessService'
import { confirmAction, notifyError, notifySuccess } from '@/utils/alerts'

export default {
  name: 'SecurityAccessRequestForm',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const submitting = ref(false)
    const users = ref([])
    const departments = ref([])
    const attachments = ref([])
    const newFiles = ref([])
    const errors = reactive({})

    const formData = reactive({
      request_number: '',
      requested_at: '',
      requestor_id: '',
      department_id: '',
      username: '',
      user_id_action: '',
      password_action: '',
      email_action: '',
      internet_access: '',
      file_sharing: '',
      vpn_action: '',
      reason: '',
      accpac_action: '',
      ifs_action: '',
      administrator_action: '',
      restore: false,
      fingerprint_action: '',
      change_data_action: '',
      notes: ''
    })

    const isEdit = computed(() => !!route.params.id)

    const actionOptions = [
      { value: 'new', label: 'New' },
      { value: 'change', label: 'Change' },
      { value: 'delete', label: 'Delete' }
    ]

    const passwordOptions = [
      { value: 'change', label: 'Change' },
      { value: 'no_change', label: 'No Change' }
    ]

    const internetOptions = [
      { value: 'control_manager', label: 'Control Manager' },
      { value: 'control_staff', label: 'Control Staff' }
    ]

    const fileSharingOptions = [
      { value: 'full_access', label: 'Full Access' },
      { value: 'modify', label: 'Modify' },
      { value: 'read_only', label: 'ReadOnly' }
    ]

    const pad = (value) => String(value).padStart(2, '0')

    const formatDateTimeLocal = (datetime) => {
      if (!datetime) return ''
      const date = new Date(datetime)
      const yyyy = date.getFullYear()
      const mm = pad(date.getMonth() + 1)
      const dd = pad(date.getDate())
      const hh = pad(date.getHours())
      const min = pad(date.getMinutes())
      const ss = pad(date.getSeconds())
      return `${yyyy}-${mm}-${dd} ${hh}:${min}:${ss}`
    }

    const formatDateTimeDisplay = (datetime) => {
      if (!datetime) return '-'
      const date = new Date(datetime)
      const yyyy = date.getFullYear()
      const mm = pad(date.getMonth() + 1)
      const dd = pad(date.getDate())
      const hh = pad(date.getHours())
      const min = pad(date.getMinutes())
      const ss = pad(date.getSeconds())
      return `${yyyy}/${mm}/${dd} ${hh}:${min}:${ss}`
    }

    const requestedAtDisplay = computed(() => formatDateTimeDisplay(formData.requested_at))

    const loadUsers = async () => {
      try {
        const { data } = await securityAccessService.getUsers()
        users.value = data.data
      } catch (error) {
        console.error('Error loading users:', error)
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

    const loadItem = async () => {
      try {
        const { data } = await securityAccessService.getSecurityAccessRequest(route.params.id)
        const item = data.data
        formData.request_number = item.request_number
        formData.requested_at = formatDateTimeLocal(item.requested_at)
        formData.requestor_id = item.requestor_id
        formData.department_id = item.department_id
        formData.username = item.username
        formData.user_id_action = item.user_id_action
        formData.password_action = item.password_action
        formData.email_action = item.email_action
        formData.internet_access = item.internet_access
        formData.file_sharing = item.file_sharing
        formData.vpn_action = item.vpn_action
        formData.reason = item.reason
        formData.accpac_action = item.accpac_action
        formData.ifs_action = item.ifs_action
        formData.administrator_action = item.administrator_action
        formData.restore = Boolean(item.restore)
        formData.fingerprint_action = item.fingerprint_action
        formData.change_data_action = item.change_data_action
        formData.notes = item.notes || ''
        attachments.value = item.attachments || []
      } catch (error) {
        console.error('Error loading request:', error)
        notifyError('Gagal memuat data security access')
        router.push('/security-access-requests')
      }
    }

    const handleFileChange = (event) => {
      newFiles.value = Array.from(event.target.files || [])
    }

    const handleRemoveAttachment = async (file) => {
      const confirmed = await confirmAction(`Hapus lampiran ${file.file_name}?`)
      if (!confirmed) return

      try {
        await securityAccessService.deleteSecurityAccessAttachment(route.params.id, file.id)
        attachments.value = attachments.value.filter(item => item.id !== file.id)
        notifySuccess('Lampiran dihapus')
      } catch (error) {
        console.error('Error deleting attachment:', error)
        notifyError(error.response?.data?.message || 'Gagal menghapus lampiran')
      }
    }

    const buildFormData = () => {
      const payload = new FormData()
      payload.append('requested_at', formData.requested_at)
      payload.append('requestor_id', formData.requestor_id)
      payload.append('department_id', formData.department_id)
      payload.append('username', formData.username)
      payload.append('user_id_action', formData.user_id_action)
      payload.append('password_action', formData.password_action)
      payload.append('email_action', formData.email_action)
      payload.append('internet_access', formData.internet_access)
      payload.append('file_sharing', formData.file_sharing)
      payload.append('vpn_action', formData.vpn_action)
      payload.append('reason', formData.reason)
      payload.append('accpac_action', formData.accpac_action)
      payload.append('ifs_action', formData.ifs_action)
      payload.append('administrator_action', formData.administrator_action)
      payload.append('restore', formData.restore ? '1' : '0')
      payload.append('fingerprint_action', formData.fingerprint_action)
      payload.append('change_data_action', formData.change_data_action)
      payload.append('notes', formData.notes || '')

      newFiles.value.forEach((file) => {
        payload.append('attachments[]', file)
      })

      return payload
    }

    const handleSubmit = async () => {
      submitting.value = true
      Object.keys(errors).forEach((key) => delete errors[key])

      try {
        const payload = buildFormData()
        if (isEdit.value) {
          await securityAccessService.updateSecurityAccessRequest(route.params.id, payload)
          notifySuccess('Security access berhasil diupdate')
        } else {
          await securityAccessService.createSecurityAccessRequest(payload)
          notifySuccess('Security access berhasil dibuat')
        }
        router.push('/security-access-requests')
      } catch (error) {
        if (error.response?.data?.errors) {
          Object.assign(errors, error.response.data.errors)
        } else {
          notifyError(error.response?.data?.message || 'Gagal menyimpan security access')
        }
      } finally {
        submitting.value = false
      }
    }

    onMounted(() => {
      formData.requested_at = formatDateTimeLocal(new Date())
      loadUsers()
      loadDepartments()
      if (isEdit.value) {
        loadItem()
      }
    })

    return {
      formData,
      users,
      departments,
      errors,
      attachments,
      submitting,
      isEdit,
      requestedAtDisplay,
      actionOptions,
      passwordOptions,
      internetOptions,
      fileSharingOptions,
      handleFileChange,
      handleRemoveAttachment,
      handleSubmit
    }
  }
}
</script>
