<template>
  <div class="login-page">
    <div class="login-card">
      <div class="card">
        <div class="card-body p-4">
          <div class="login-brand">
            <div class="login-badge">SAP R/3</div>
            <div>
              <h2 class="mb-1">ITGC System</h2>
              <p class="text-muted mb-0">IT General Control Management</p>
            </div>
          </div>

          <div v-if="error" class="alert alert-danger" role="alert">
            {{ error }}
          </div>

          <form @submit.prevent="handleLogin">
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

            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input
                type="password"
                class="form-control"
                id="password"
                v-model="form.password"
                required
                placeholder="Enter your password"
              />
            </div>

            <div class="mb-3 form-check">
              <input
                type="checkbox"
                class="form-check-input"
                id="remember"
                v-model="form.remember"
              />
              <label class="form-check-label" for="remember">
                Remember me
              </label>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
              <router-link to="/auth/forgot-password" class="small">
                Forgot password?
              </router-link>
              <router-link to="/auth/register" class="small">
                Create account
              </router-link>
            </div>

            <button
              type="submit"
              class="btn btn-primary w-100"
              :disabled="loading"
            >
              <span v-if="loading">
                <span class="spinner-border spinner-border-sm me-2"></span>
                Loading...
              </span>
              <span v-else>Login</span>
            </button>
          </form>

          <div class="text-center mt-3">
            <small class="text-muted">
              Default credentials: admin@itgc.com / password
            </small>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import authService from '@/services/authService'

const router = useRouter()
const loading = ref(false)
const error = ref('')

const form = ref({
  email: '',
  password: '',
  remember: false
})

const handleLogin = async () => {
  loading.value = true
  error.value = ''

  try {
    await authService.login({
      email: form.value.email,
      password: form.value.password
    })

    router.push('/dashboard')
  } catch (err) {
    error.value = err.message || 'Login failed. Please check your credentials.'
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
