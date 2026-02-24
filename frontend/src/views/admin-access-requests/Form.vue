<template>
  <div class="container-fluid py-4">
    <div class="mb-4">
      <router-link to="/admin-access-requests" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar
      </router-link>
      <h2 class="h4">{{ isEdit ? 'Edit' : 'Buat' }} Peminjaman Akses Administrator</h2>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <form @submit.prevent="handleSubmit">
              <div class="row g-3">
                <div class="col-12" v-if="isEdit">
                  <label class="form-label">No</label>
                  <input type="text" class="form-control" :value="formData.request_number" disabled />
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
                  <label class="form-label">Request Type <span class="text-danger">*</span></label>
                  <Select2
                    v-model="formData.request_type"
                    :options="requestTypeOptions"
                    placeholder="Pilih Tipe"
                    :invalid="!!errors.request_type"
                  />
                  <div v-if="errors.request_type" class="invalid-feedback">
                    {{ errors.request_type[0] }}
                  </div>
                </div>

                <div class="col-md-3">
                  <label class="form-label">Durasi <span class="text-danger">*</span></label>
                  <input
                    type="number"
                    min="1"
                    class="form-control"
                    v-model.number="formData.duration_value"
                    :class="{ 'is-invalid': errors.duration_value }"
                    placeholder="0"
                  />
                  <div v-if="errors.duration_value" class="invalid-feedback">
                    {{ errors.duration_value[0] }}
                  </div>
                </div>

                <div class="col-md-3">
                  <label class="form-label">Satuan <span class="text-danger">*</span></label>
                  <Select2
                    v-model="formData.duration_unit"
                    :options="durationUnitOptions"
                    placeholder="Pilih Satuan"
                    :invalid="!!errors.duration_unit"
                  />
                  <div v-if="errors.duration_unit" class="invalid-feedback">
                    {{ errors.duration_unit[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Metode <span class="text-danger">*</span></label>
                  <Select2
                    v-model="formData.method"
                    :options="methodOptions"
                    placeholder="Pilih Metode"
                    :invalid="!!errors.method"
                  />
                  <div v-if="errors.method" class="invalid-feedback">
                    {{ errors.method[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Hostname <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="formData.hostname"
                    :class="{ 'is-invalid': errors.hostname }"
                    placeholder="Hostname"
                  />
                  <div v-if="errors.hostname" class="invalid-feedback">
                    {{ errors.hostname[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">UserAdministrator <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="formData.user_administrator"
                    :class="{ 'is-invalid': errors.user_administrator }"
                    placeholder="User Administrator"
                  />
                  <div v-if="errors.user_administrator" class="invalid-feedback">
                    {{ errors.user_administrator[0] }}
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label">Tujuan <span class="text-danger">*</span></label>
                  <textarea
                    class="form-control"
                    v-model="formData.purpose"
                    :class="{ 'is-invalid': errors.purpose }"
                    rows="3"
                    placeholder="Jelaskan tujuan..."
                  ></textarea>
                  <div v-if="errors.purpose" class="invalid-feedback">
                    {{ errors.purpose[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Tanggal (Request)</label>
                  <input type="text" class="form-control" :value="requestedAtDisplay" disabled />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Partner</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="formData.partner"
                    :class="{ 'is-invalid': errors.partner }"
                    placeholder="Partner (opsional)"
                  />
                  <div v-if="errors.partner" class="invalid-feedback">
                    {{ errors.partner[0] }}
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label">Keterangan</label>
                  <textarea
                    class="form-control"
                    v-model="formData.notes"
                    :class="{ 'is-invalid': errors.notes }"
                    rows="3"
                    placeholder="Keterangan (opsional)"
                  ></textarea>
                  <div v-if="errors.notes" class="invalid-feedback">
                    {{ errors.notes[0] }}
                  </div>
                </div>
              </div>

              <div class="mt-4">
                <button type="submit" class="btn btn-danger me-2" :disabled="submitting">
                  <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                  {{ isEdit ? 'Update' : 'Buat' }} Permintaan
                </button>
                <router-link to="/admin-access-requests" class="btn btn-outline-secondary">
                  Batal
                </router-link>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card">
          <div class="card-header bg-white">
            <h6 class="mb-0">Panduan</h6>
          </div>
          <div class="card-body">
            <ul class="small mb-0">
              <li><strong>Request Type:</strong> Temporary, Permanent, Emergency, Maintenance</li>
              <li><strong>Durasi:</strong> Isi angka dan pilih Jam/Hari</li>
              <li><strong>Metode:</strong> VPN, RDP, Local, Server Console, Others</li>
              <li><strong>Status:</strong> Pending, Approved, Rejected, Expired</li>
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
import adminAccessRequestService from '@/services/adminAccessRequestService'
import { notifyError, notifySuccess } from '@/utils/alerts'

export default {
  name: 'AdminAccessRequestForm',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const submitting = ref(false)
    const users = ref([])
    const departments = ref([])
    const errors = reactive({})

    const formData = reactive({
      request_number: '',
      requestor_id: '',
      department_id: '',
      request_type: '',
      duration_value: '',
      duration_unit: '',
      method: '',
      hostname: '',
      user_administrator: '',
      purpose: '',
      requested_at: '',
      partner: '',
      notes: ''
    })

    const isEdit = computed(() => !!route.params.id)

    const requestTypeOptions = [
      { value: 'temporary', label: 'Temporary' },
      { value: 'permanent', label: 'Permanent' },
      { value: 'emergency', label: 'Emergency' },
      { value: 'maintenance', label: 'Maintenance' }
    ]

    const durationUnitOptions = [
      { value: 'hour', label: 'Jam' },
      { value: 'day', label: 'Hari' }
    ]

    const methodOptions = [
      { value: 'vpn', label: 'VPN' },
      { value: 'rdp', label: 'RDP' },
      { value: 'local', label: 'Local' },
      { value: 'server_console', label: 'Server Console' },
      { value: 'others', label: 'Others' }
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
        const { data } = await adminAccessRequestService.getUsers()
        users.value = data.data
      } catch (error) {
        console.error('Error loading users:', error)
      }
    }

    const loadDepartments = async () => {
      try {
        const { data } = await adminAccessRequestService.getDepartments()
        departments.value = data.data || data
      } catch (error) {
        console.error('Error loading departments:', error)
      }
    }

    const loadRequest = async () => {
      try {
        const { data } = await adminAccessRequestService.getAdminAccessRequest(route.params.id)
        const requestItem = data.data
        formData.request_number = requestItem.request_number
        formData.requestor_id = requestItem.requestor_id
        formData.department_id = requestItem.department_id
        formData.request_type = requestItem.request_type
        formData.duration_value = requestItem.duration_value
        formData.duration_unit = requestItem.duration_unit
        formData.method = requestItem.method
        formData.hostname = requestItem.hostname
        formData.user_administrator = requestItem.user_administrator
        formData.purpose = requestItem.purpose
        formData.requested_at = formatDateTimeLocal(requestItem.requested_at)
        formData.partner = requestItem.partner || ''
        formData.notes = requestItem.notes || ''
      } catch (error) {
        console.error('Error loading request:', error)
        notifyError('Gagal memuat data permintaan')
        router.push('/admin-access-requests')
      }
    }

    const handleSubmit = async () => {
      submitting.value = true
      Object.keys(errors).forEach((key) => delete errors[key])

      try {
        const payload = {
          ...formData,
          requested_at: formData.requested_at || formatDateTimeLocal(new Date())
        }

        if (isEdit.value) {
          await adminAccessRequestService.updateAdminAccessRequest(route.params.id, payload)
          notifySuccess('Permintaan berhasil diperbarui')
        } else {
          await adminAccessRequestService.createAdminAccessRequest(payload)
          notifySuccess('Permintaan berhasil dibuat')
        }

        router.push('/admin-access-requests')
      } catch (error) {
        if (error.response?.status === 422 && error.response?.data?.errors) {
          Object.assign(errors, error.response.data.errors)
        } else {
          notifyError(error.response?.data?.message || 'Gagal menyimpan permintaan')
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
        loadRequest()
      }
    })

    return {
      submitting,
      users,
      departments,
      errors,
      formData,
      requestTypeOptions,
      durationUnitOptions,
      methodOptions,
      requestedAtDisplay,
      isEdit,
      handleSubmit
    }
  }
}
</script>
