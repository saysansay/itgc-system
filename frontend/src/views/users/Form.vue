<template>
  <div class="container-fluid py-4">
    <div class="mb-4">
      <router-link to="/users" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left me-2"></i>Back to Users
      </router-link>
      <h2 class="h4">{{ isEdit ? 'Edit User' : 'Create User' }}</h2>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <form @submit.prevent="handleSubmit">
              <div class="row g-3">
                <!-- Employee ID -->
                <div class="col-md-6">
                  <label class="form-label">Employee ID <span class="text-danger">*</span></label>
                  <input 
                    type="text" 
                    class="form-control" 
                    v-model="form.employee_id"
                    :class="{ 'is-invalid': errors.employee_id }"
                    required
                  >
                  <div v-if="errors.employee_id" class="invalid-feedback">
                    {{ errors.employee_id[0] }}
                  </div>
                </div>

                <!-- Name -->
                <div class="col-md-6">
                  <label class="form-label">Full Name <span class="text-danger">*</span></label>
                  <input 
                    type="text" 
                    class="form-control" 
                    v-model="form.name"
                    :class="{ 'is-invalid': errors.name }"
                    required
                  >
                  <div v-if="errors.name" class="invalid-feedback">
                    {{ errors.name[0] }}
                  </div>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                  <label class="form-label">Email <span class="text-danger">*</span></label>
                  <input 
                    type="email" 
                    class="form-control" 
                    v-model="form.email"
                    :class="{ 'is-invalid': errors.email }"
                    required
                  >
                  <div v-if="errors.email" class="invalid-feedback">
                    {{ errors.email[0] }}
                  </div>
                </div>

                <!-- Phone -->
                <div class="col-md-6">
                  <label class="form-label">Phone</label>
                  <input 
                    type="text" 
                    class="form-control" 
                    v-model="form.phone"
                    :class="{ 'is-invalid': errors.phone }"
                  >
                  <div v-if="errors.phone" class="invalid-feedback">
                    {{ errors.phone[0] }}
                  </div>
                </div>

                <!-- Position -->
                <div class="col-md-6">
                  <label class="form-label">Position</label>
                  <input 
                    type="text" 
                    class="form-control" 
                    v-model="form.position"
                    :class="{ 'is-invalid': errors.position }"
                  >
                  <div v-if="errors.position" class="invalid-feedback">
                    {{ errors.position[0] }}
                  </div>
                </div>

                <!-- Department -->
                <div class="col-md-6">
                  <label class="form-label">Department</label>
                  <Select2
                    v-model="form.department_id"
                    :options="departments"
                    label-key="name"
                    value-key="id"
                    placeholder="- Select Department -"
                    :invalid="!!errors.department_id"
                  />
                  <div v-if="errors.department_id" class="invalid-feedback">
                    {{ errors.department_id[0] }}
                  </div>
                </div>

                <!-- Roles -->
                <div class="col-12">
                  <label class="form-label">Roles <span class="text-danger">*</span></label>
                  <div class="border rounded p-3" :class="{ 'border-danger': errors.role_ids }">
                    <div v-for="role in roles" :key="role.id" class="form-check mb-2">
                      <input 
                        class="form-check-input" 
                        type="checkbox" 
                        :id="`role-${role.id}`"
                        :value="role.id"
                        v-model="form.role_ids"
                      >
                      <label class="form-check-label" :for="`role-${role.id}`">
                        <strong>{{ role.name }}</strong>
                        <small class="text-muted d-block">{{ role.description }}</small>
                      </label>
                    </div>
                  </div>
                  <div v-if="errors.role_ids" class="text-danger small mt-1">
                    {{ errors.role_ids[0] }}
                  </div>
                </div>

                <!-- Password (only when creating new user or changing) -->
                <div class="col-12">
                  <hr>
                  <h6>{{ isEdit ? 'Change Password (leave empty to keep current)' : 'Password' }}</h6>
                </div>

                <div class="col-md-6">
                  <label class="form-label">
                    Password <span v-if="!isEdit" class="text-danger">*</span>
                  </label>
                  <input 
                    type="password" 
                    class="form-control" 
                    v-model="form.password"
                    :class="{ 'is-invalid': errors.password }"
                    :required="!isEdit"
                  >
                  <div v-if="errors.password" class="invalid-feedback">
                    {{ errors.password[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">
                    Password Confirmation <span v-if="!isEdit" class="text-danger">*</span>
                  </label>
                  <input 
                    type="password" 
                    class="form-control" 
                    v-model="form.password_confirmation"
                    :required="!isEdit || form.password"
                  >
                </div>

                <!-- Status -->
                <div class="col-12">
                  <div class="form-check form-switch">
                    <input 
                      class="form-check-input" 
                      type="checkbox" 
                      id="is_active"
                      v-model="form.is_active"
                    >
                    <label class="form-check-label" for="is_active">
                      Active
                    </label>
                  </div>
                </div>

                <!-- Submit -->
                <div class="col-12">
                  <hr>
                  <button type="submit" class="btn btn-danger" :disabled="loading">
                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                    {{ isEdit ? 'Update User' : 'Create User' }}
                  </button>
                  <router-link to="/users" class="btn btn-secondary ms-2">Cancel</router-link>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import userService from '@/services/userService'
import { notifyError, notifySuccess } from '@/utils/alerts'

export default {
  name: 'UserForm',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const loading = ref(false)
    const roles = ref([])
    const departments = ref([])
    const errors = reactive({})
    
    const form = reactive({
      employee_id: '',
      name: '',
      email: '',
      phone: '',
      position: '',
      department_id: '',
      role_ids: [],
      password: '',
      password_confirmation: '',
      is_active: true
    })

    const isEdit = computed(() => !!route.params.id)

    const loadRoles = async () => {
      try {
        const { data } = await userService.getRoles()
        roles.value = data
      } catch (error) {
        console.error('Error loading roles:', error)
      }
    }

    const loadDepartments = async () => {
      try {
        const { data } = await userService.getDepartments()
        departments.value = data
      } catch (error) {
        console.error('Error loading departments:', error)
      }
    }

    const loadUser = async () => {
      if (!isEdit.value) return
      
      loading.value = true
      try {
        const { data } = await userService.getUser(route.params.id)
        form.employee_id = data.data.employee_id
        form.name = data.data.name
        form.email = data.data.email
        form.phone = data.data.phone || ''
        form.position = data.data.position || ''
        form.department_id = data.data.department_id || ''
        form.role_ids = data.data.roles.map(r => r.id)
        form.is_active = data.data.is_active
      } catch (error) {
        console.error('Error loading user:', error)
        notifyError('Failed to load user data')
        router.push('/users')
      } finally {
        loading.value = false
      }
    }

    const handleSubmit = async () => {
      loading.value = true
      Object.keys(errors).forEach(key => delete errors[key])

      try {
        if (isEdit.value) {
          await userService.updateUser(route.params.id, form)
          notifySuccess('User updated successfully')
        } else {
          await userService.createUser(form)
          notifySuccess('User created successfully')
        }
        router.push('/users')
      } catch (error) {
        console.error('Error saving user:', error)
        if (error.response?.data?.errors) {
          Object.assign(errors, error.response.data.errors)
        } else {
          notifyError(error.response?.data?.message || 'Failed to save user')
        }
      } finally {
        loading.value = false
      }
    }

    onMounted(async () => {
      await Promise.all([loadRoles(), loadDepartments()])
      if (isEdit.value) {
        await loadUser()
      }
    })

    return {
      loading,
      form,
      errors,
      roles,
      departments,
      isEdit,
      handleSubmit
    }
  }
}
</script>
