<template>
  <div class="container-fluid py-4">
    <div class="mb-4">
      <router-link to="/general-troubles" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar
      </router-link>
      <h2 class="h4">{{ isEdit ? 'Edit' : 'Buat' }} General Trouble</h2>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <form @submit.prevent="handleSubmit">
              <div class="row g-3">
                <div class="col-12" v-if="isEdit">
                  <label class="form-label">No</label>
                  <input type="text" class="form-control" :value="formData.trouble_number" disabled />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                  <FlexDate
                    v-model="formData.reported_at"
                    :config="dateTimeConfig"
                    :invalid="!!errors.reported_at"
                  />
                  <div v-if="errors.reported_at" class="invalid-feedback">
                    {{ errors.reported_at[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Users <span class="text-danger">*</span></label>
                  <Select2
                    v-model="formData.user_id"
                    :options="users"
                    label-key="name"
                    value-key="id"
                    placeholder="Pilih User"
                    :invalid="!!errors.user_id"
                  />
                  <div v-if="errors.user_id" class="invalid-feedback">
                    {{ errors.user_id[0] }}
                  </div>
                </div>

                <div class="col-md-4">
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

                <div class="col-md-4">
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

                <div class="col-md-4">
                  <label class="form-label">Type <span class="text-danger">*</span></label>
                  <Select2
                    v-model="formData.type"
                    :options="typeOptions"
                    placeholder="Pilih Type"
                    :invalid="!!errors.type"
                  />
                  <div v-if="errors.type" class="invalid-feedback">
                    {{ errors.type[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">PIC (IT) <span class="text-danger">*</span></label>
                  <Select2
                    v-model="formData.pic_id"
                    :options="itUsers"
                    label-key="name"
                    value-key="id"
                    placeholder="Pilih PIC"
                    :invalid="!!errors.pic_id"
                  />
                  <div v-if="errors.pic_id" class="invalid-feedback">
                    {{ errors.pic_id[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Partner (Vendor)</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="formData.partner"
                    :class="{ 'is-invalid': errors.partner }"
                    placeholder="Partner eksternal"
                  />
                  <div v-if="errors.partner" class="invalid-feedback">
                    {{ errors.partner[0] }}
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label">Problem <span class="text-danger">*</span></label>
                  <textarea
                    class="form-control"
                    v-model="formData.problem"
                    :class="{ 'is-invalid': errors.problem }"
                    rows="3"
                    placeholder="Deskripsikan problem"
                  ></textarea>
                  <div v-if="errors.problem" class="invalid-feedback">
                    {{ errors.problem[0] }}
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label">Analisa <span class="text-danger">*</span></label>
                  <textarea
                    class="form-control"
                    v-model="formData.analysis"
                    :class="{ 'is-invalid': errors.analysis }"
                    rows="3"
                    placeholder="Analisa penyebab"
                  ></textarea>
                  <div v-if="errors.analysis" class="invalid-feedback">
                    {{ errors.analysis[0] }}
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label">Solusi <span class="text-danger">*</span></label>
                  <textarea
                    class="form-control"
                    v-model="formData.solution"
                    :class="{ 'is-invalid': errors.solution }"
                    rows="3"
                    placeholder="Solusi yang dilakukan"
                  ></textarea>
                  <div v-if="errors.solution" class="invalid-feedback">
                    {{ errors.solution[0] }}
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label">Keterangan</label>
                  <textarea
                    class="form-control"
                    v-model="formData.notes"
                    :class="{ 'is-invalid': errors.notes }"
                    rows="3"
                    placeholder="Keterangan tambahan (opsional)"
                  ></textarea>
                  <div v-if="errors.notes" class="invalid-feedback">
                    {{ errors.notes[0] }}
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Status <span class="text-danger">*</span></label>
                  <Select2
                    v-model="formData.status"
                    :options="statusOptions"
                    placeholder="Pilih Status"
                    :invalid="!!errors.status"
                  />
                  <div v-if="errors.status" class="invalid-feedback">
                    {{ errors.status[0] }}
                  </div>
                </div>
              </div>

              <div class="mt-4">
                <button type="submit" class="btn btn-danger me-2" :disabled="submitting">
                  <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                  {{ isEdit ? 'Update' : 'Simpan' }} Data
                </button>
                <router-link to="/general-troubles" class="btn btn-outline-secondary">
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
              <li><strong>Type:</strong> Hardware, Software, Network, Security, Others</li>
              <li><strong>Durasi:</strong> Isi angka dan pilih Menit/Jam</li>
              <li><strong>Status:</strong> Open, On Progress, Done, Closed</li>
              <li><strong>PIC:</strong> User dengan role IT/Admin</li>
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
import generalTroubleService from '@/services/generalTroubleService'
import { notifyError, notifySuccess } from '@/utils/alerts'

export default {
  name: 'GeneralTroubleForm',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const submitting = ref(false)
    const users = ref([])
    const itUsers = ref([])
    const errors = reactive({})

    const formData = reactive({
      trouble_number: '',
      reported_at: '',
      user_id: '',
      duration_value: '',
      duration_unit: '',
      problem: '',
      analysis: '',
      solution: '',
      type: '',
      pic_id: '',
      partner: '',
      notes: '',
      status: 'open'
    })

    const isEdit = computed(() => !!route.params.id)

    const dateTimeConfig = {
      enableTime: true,
      enableSeconds: true,
      time_24hr: true,
      dateFormat: 'Y-m-d H:i:S'
    }

    const durationUnitOptions = [
      { value: 'minute', label: 'Menit' },
      { value: 'hour', label: 'Jam' }
    ]

    const typeOptions = [
      { value: 'hardware', label: 'Hardware' },
      { value: 'software', label: 'Software' },
      { value: 'network', label: 'Network' },
      { value: 'security', label: 'Security' },
      { value: 'others', label: 'Others' }
    ]

    const statusOptions = [
      { value: 'open', label: 'Open' },
      { value: 'on_progress', label: 'On Progress' },
      { value: 'done', label: 'Done' },
      { value: 'closed', label: 'Closed' }
    ]

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

    const loadItem = async () => {
      try {
        const { data } = await generalTroubleService.getGeneralTrouble(route.params.id)
        const item = data.data
        formData.trouble_number = item.trouble_number
        formData.reported_at = formatDateTimeLocal(item.reported_at)
        formData.user_id = item.user_id
        formData.duration_value = item.duration_value
        formData.duration_unit = item.duration_unit
        formData.problem = item.problem
        formData.analysis = item.analysis
        formData.solution = item.solution
        formData.type = item.type
        formData.pic_id = item.pic_id
        formData.partner = item.partner || ''
        formData.notes = item.notes || ''
        formData.status = item.status
      } catch (error) {
        console.error('Error loading general trouble:', error)
        notifyError('Gagal memuat data general trouble')
        router.push('/general-troubles')
      }
    }

    const handleSubmit = async () => {
      submitting.value = true
      Object.keys(errors).forEach(key => delete errors[key])

      try {
        if (isEdit.value) {
          await generalTroubleService.updateGeneralTrouble(route.params.id, formData)
          notifySuccess('General trouble berhasil diupdate')
        } else {
          await generalTroubleService.createGeneralTrouble(formData)
          notifySuccess('General trouble berhasil dibuat')
        }
        router.push('/general-troubles')
      } catch (error) {
        if (error.response?.data?.errors) {
          Object.assign(errors, error.response.data.errors)
        } else {
          notifyError(error.response?.data?.message || 'Gagal menyimpan general trouble')
        }
      } finally {
        submitting.value = false
      }
    }

    onMounted(() => {
      loadUsers()
      loadItUsers()
      if (isEdit.value) {
        loadItem()
      }
    })

    return {
      formData,
      errors,
      users,
      itUsers,
      submitting,
      isEdit,
      dateTimeConfig,
      durationUnitOptions,
      typeOptions,
      statusOptions,
      handleSubmit
    }
  }
}
</script>
