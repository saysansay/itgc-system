<template>
  <div class="container-fluid py-4">
    <div class="mb-4">
      <h2 class="h4">Change Password</h2>
    </div>

    <div class="row">
      <div class="col-md-6 col-lg-5">
        <div class="card">
          <div class="card-body">
            <form @submit.prevent="handleSubmit">
              <div class="mb-3">
                <label class="form-label">Current Password <span class="text-danger">*</span></label>
                <input
                  type="password"
                  class="form-control"
                  v-model="form.current_password"
                  :class="{ 'is-invalid': errors.current_password }"
                  autocomplete="current-password"
                />
                <div v-if="errors.current_password" class="invalid-feedback">
                  {{ errors.current_password[0] }}
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">New Password <span class="text-danger">*</span></label>
                <input
                  type="password"
                  class="form-control"
                  v-model="form.new_password"
                  :class="{ 'is-invalid': errors.new_password }"
                  autocomplete="new-password"
                />
                <div v-if="errors.new_password" class="invalid-feedback">
                  {{ errors.new_password[0] }}
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                <input
                  type="password"
                  class="form-control"
                  v-model="form.new_password_confirmation"
                  :class="{ 'is-invalid': errors.new_password }"
                  autocomplete="new-password"
                />
              </div>

              <button type="submit" class="btn btn-danger" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                Update Password
              </button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-lg-5">
        <div class="card">
          <div class="card-header bg-white">
            <h6 class="mb-0">Tips</h6>
          </div>
          <div class="card-body">
            <ul class="small mb-0">
              <li>Minimal 8 karakter.</li>
              <li>Gunakan kombinasi huruf besar, kecil, angka, dan simbol.</li>
              <li>Jangan gunakan password lama.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'
import authService from '@/services/authService'
import { notifyError, notifySuccess } from '@/utils/alerts'

export default {
  name: 'ChangePassword',
  setup() {
    const loading = ref(false)
    const errors = reactive({})
    const form = reactive({
      current_password: '',
      new_password: '',
      new_password_confirmation: ''
    })

    const resetForm = () => {
      form.current_password = ''
      form.new_password = ''
      form.new_password_confirmation = ''
    }

    const handleSubmit = async () => {
      loading.value = true
      Object.keys(errors).forEach((key) => delete errors[key])

      try {
        await authService.changePassword(form)
        notifySuccess('Password berhasil diubah')
        resetForm()
      } catch (error) {
        if (error?.errors) {
          Object.assign(errors, error.errors)
        } else {
          notifyError(error?.message || 'Gagal mengubah password')
        }
      } finally {
        loading.value = false
      }
    }

    return {
      loading,
      errors,
      form,
      handleSubmit
    }
  }
}
</script>
