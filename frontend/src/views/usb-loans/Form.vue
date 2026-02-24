<template>
  <div class="container-fluid py-4">
    <div class="mb-4">
      <router-link to="/usb-loans" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar Peminjaman
      </router-link>
      <h2 class="h4">{{ isEdit ? 'Edit' : 'Buat' }} Peminjaman USB</h2>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <form @submit.prevent="handleSubmit">
              <div class="row g-3">
                <div class="col-12" v-if="isEdit">
                  <label class="form-label">No. Peminjaman</label>
                  <input
                    type="text"
                    class="form-control"
                    :value="formData.loan_number"
                    disabled
                  />
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
                  <label class="form-label">Department <span class="text-danger">*</span></label>
                  <Select2
                    v-model="formData.department_id"
                    :options="departments"
                    label-key="name"
                    value-key="id"
                    placeholder="Pilih Department"
                    :invalid="!!errors.department_id"
                  />
                  <div v-if="errors.department_id" class="invalid-feedback">
                    {{ errors.department_id[0] }}
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label">Tujuan Peminjaman <span class="text-danger">*</span></label>
                  <textarea
                    class="form-control"
                    v-model="formData.purpose"
                    :class="{ 'is-invalid': errors.purpose }"
                    rows="3"
                    placeholder="Jelaskan tujuan peminjaman USB..."
                    required
                  ></textarea>
                  <div v-if="errors.purpose" class="invalid-feedback">
                    {{ errors.purpose[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Tanggal & Jam Peminjaman <span class="text-danger">*</span></label>
                  <FlexDate
                    v-model="formData.loan_datetime"
                    :config="dateTimeConfig"
                    :invalid="!!errors.loan_datetime"
                  />
                  <div v-if="errors.loan_datetime" class="invalid-feedback">
                    {{ errors.loan_datetime[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Tanggal & Jam Pengembalian</label>
                  <FlexDate
                    v-model="formData.return_datetime"
                    :config="dateTimeConfig"
                    :invalid="!!errors.return_datetime"
                  />
                  <div v-if="errors.return_datetime" class="invalid-feedback">
                    {{ errors.return_datetime[0] }}
                  </div>
                  <small class="text-muted">Opsional - Rencana waktu pengembalian</small>
                </div>

                <div class="col-md-6">
                  <label class="form-label">PIC (IT/Admin) <span class="text-danger">*</span></label>
                  <Select2
                    v-model="formData.pic_id"
                    :options="itAdminUsers"
                    label-key="name"
                    value-key="id"
                    placeholder="Pilih PIC"
                    :invalid="!!errors.pic_id"
                  />
                  <div v-if="errors.pic_id" class="invalid-feedback">
                    {{ errors.pic_id[0] }}
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label">Keterangan</label>
                  <textarea
                    class="form-control"
                    v-model="formData.notes"
                    :class="{ 'is-invalid': errors.notes }"
                    rows="3"
                    placeholder="Catatan tambahan (opsional)..."
                  ></textarea>
                  <div v-if="errors.notes" class="invalid-feedback">
                    {{ errors.notes[0] }}
                  </div>
                </div>
              </div>

              <div class="mt-4">
                <button type="submit" class="btn btn-danger me-2" :disabled="submitting">
                  <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                  {{ isEdit ? 'Update' : 'Buat' }} Peminjaman
                </button>
                <router-link to="/usb-loans" class="btn btn-outline-secondary">
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
            <h6 class="mb-0">Panduan Peminjaman USB</h6>
          </div>
          <div class="card-body">
            <ul class="small mb-0">
              <li><strong>Requestor:</strong> Peminjam USB</li>
              <li><strong>Department:</strong> Department peminjam</li>
              <li><strong>Tujuan:</strong> Jelaskan secara detail untuk keperluan apa USB dipinjam</li>
              <li><strong>PIC:</strong> Penanggung jawab dari IT/Admin yang menyetujui peminjaman</li>
              <li><strong>Status:</strong>
                <ul class="mt-1">
                  <li><strong>Pending:</strong> Menunggu persetujuan</li>
                  <li><strong>Approved:</strong> Disetujui, USB dapat dipinjam</li>
                  <li><strong>Rejected:</strong> Ditolak</li>
                  <li><strong>Returned:</strong> USB sudah dikembalikan</li>
                </ul>
              </li>
              <li class="mt-2">Pastikan mengembalikan USB sesuai jadwal yang ditentukan</li>
              <li>Jaga USB dengan baik selama peminjaman</li>
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
import usbLoanService from '@/services/usbLoanService'
import { notifyError, notifySuccess } from '@/utils/alerts'

export default {
  name: 'UsbLoanForm',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const submitting = ref(false)
    const users = ref([])
    const departments = ref([])
    const itAdminUsers = ref([])
    const errors = reactive({})
    const formData = reactive({
      loan_number: '',
      requestor_id: '',
      department_id: '',
      purpose: '',
      loan_datetime: '',
      return_datetime: '',
      pic_id: '',
      notes: ''
    })

    const dateTimeConfig = {
      enableTime: true,
      enableSeconds: true,
      time_24hr: true,
      dateFormat: 'Y-m-d H:i:S'
    }

    const isEdit = computed(() => !!route.params.id)

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
        const { data } = await usbLoanService.getUsers()
        users.value = data.data
      } catch (error) {
        console.error('Error loading users:', error)
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

    const loadItAdminUsers = async () => {
      try {
        const { data } = await usbLoanService.getItAdminUsers()
        itAdminUsers.value = data.data
      } catch (error) {
        console.error('Error loading IT/Admin users:', error)
      }
    }

    const loadUsbLoan = async () => {
      try {
        const { data } = await usbLoanService.getUsbLoan(route.params.id)
        const loan = data.data
        formData.loan_number = loan.loan_number
        formData.requestor_id = loan.requestor_id
        formData.department_id = loan.department_id
        formData.purpose = loan.purpose
        formData.loan_datetime = formatDateTimeLocal(loan.loan_datetime)
        formData.return_datetime = formatDateTimeLocal(loan.return_datetime)
        formData.pic_id = loan.pic_id
        formData.notes = loan.notes || ''
      } catch (error) {
        console.error('Error loading USB loan:', error)
        notifyError('Gagal memuat data peminjaman USB')
        router.push('/usb-loans')
      }
    }

    const handleSubmit = async () => {
      submitting.value = true
      Object.keys(errors).forEach(key => delete errors[key])

      try {
        if (isEdit.value) {
          await usbLoanService.updateUsbLoan(route.params.id, formData)
          notifySuccess('Peminjaman USB berhasil diupdate')
        } else {
          await usbLoanService.createUsbLoan(formData)
          notifySuccess('Peminjaman USB berhasil dibuat')
        }
        router.push('/usb-loans')
      } catch (error) {
        if (error.response?.data?.errors) {
          Object.assign(errors, error.response.data.errors)
        } else {
          notifyError(error.response?.data?.message || 'Gagal menyimpan peminjaman USB')
        }
      } finally {
        submitting.value = false
      }
    }

    onMounted(() => {
      loadUsers()
      loadDepartments()
      loadItAdminUsers()
      if (isEdit.value) {
        loadUsbLoan()
      }
    })

    return {
      formData,
      errors,
      submitting,
      users,
      departments,
      itAdminUsers,
      isEdit,
      dateTimeConfig,
      handleSubmit
    }
  }
}
</script>
