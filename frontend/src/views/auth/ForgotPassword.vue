<template>
  <div class="login-page">
    <div class="login-card">
      <div class="card">
        <div class="card-body p-4">
          <div class="login-brand">
            <div class="login-badge">SAP R/3</div>
            <div>
              <h2 class="mb-1">Forgot Password</h2>
              <p class="text-muted mb-0">Reset link will be sent to your email</p>
            </div>
          </div>

          <div v-if="error" class="alert alert-danger" role="alert">
            {{ error }}
          </div>
          <div v-if="success" class="alert alert-success" role="alert">
            {{ success }}
          </div>

          <form @submit.prevent="handleSubmit">
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input
                type="email"
                class="form-control"
                id="email"
                v-model="form.email"
                required
                placeholder="Enter your email"
              />
            </div>

            <button
              type="submit"
              class="btn btn-primary w-100"
              :disabled="loading"
            >
              <span v-if="loading">
                <span class="spinner-border spinner-border-sm me-2"></span>
                Sending...
              </span>
              <span v-else>Send Reset Link</span>
            </button>
          </form>

          <div class="text-center mt-3">
            <router-link to="/auth/login" class="small">Back to login</router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import authService from '@/services/authService'

const loading = ref(false)
const error = ref('')
const success = ref('')

const form = ref({
  email: ''
})

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  success.value = ''

  try {
    const response = await authService.forgotPassword({
      email: form.value.email
    })
    success.value = response.message || 'Reset link sent to your email.'
  } catch (err) {
    error.value = err.message || 'Failed to send reset link.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: transparent;
}

.login-card {
  width: 100%;
  max-width: 360px;
}

.login-card .card {
  border: 1px solid #d3d9e3;
  border-top: 3px solid rgba(199, 70, 52, 0.65);
  border-radius: 12px;
  box-shadow:
    0 12px 26px rgba(15, 23, 42, 0.08),
    0 2px 6px rgba(15, 23, 42, 0.06);
  background: #ffffff;
}

.login-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 16px;
}

.login-brand h2 {
  font-size: 17px;
  font-weight: 600;
  color: #1f1f1f;
}

.login-badge {
  background: rgba(199, 70, 52, 0.15);
  color: #8a1f11;
  font-size: 11px;
  font-weight: 700;
  padding: 6px 10px;
  border-radius: 10px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

@media (max-width: 992px) {
  .login-page {
    padding: 20px;
  }
}
</style>
