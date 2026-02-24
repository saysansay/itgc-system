<template>
  <div class="container-fluid py-4">
    <div class="mb-4">
      <router-link to="/departments" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left me-2"></i>Back to Departments
      </router-link>
      <h2 class="h4">{{ isEdit ? 'Edit Department' : 'Create Department' }}</h2>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <form @submit.prevent="handleSubmit">
              <div class="row g-3">
                <!-- Department Code -->
                <div class="col-md-6">
                  <label class="form-label">Department Code <span class="text-danger">*</span></label>
                  <input 
                    type="text" 
                    class="form-control" 
                    v-model="form.code"
                    :class="{ 'is-invalid': errors.code }"
                    required
                    placeholder="e.g., IT, HR, FIN"
                  >
                  <div v-if="errors.code" class="invalid-feedback">
                    {{ errors.code[0] }}
                  </div>
                </div>

                <!-- Department Name -->
                <div class="col-md-6">
                  <label class="form-label">Department Name <span class="text-danger">*</span></label>
                  <input 
                    type="text" 
                    class="form-control" 
                    v-model="form.name"
                    :class="{ 'is-invalid': errors.name }"
                    required
                    placeholder="e.g., Information Technology"
                  >
                  <div v-if="errors.name" class="invalid-feedback">
                    {{ errors.name[0] }}
                  </div>
                </div>

                <!-- Description -->
                <div class="col-12">
                  <label class="form-label">Description</label>
                  <textarea 
                    class="form-control" 
                    v-model="form.description"
                    :class="{ 'is-invalid': errors.description }"
                    rows="3"
                    placeholder="Describe the department's responsibilities and functions..."
                  ></textarea>
                  <div v-if="errors.description" class="invalid-feedback">
                    {{ errors.description[0] }}
                  </div>
                </div>

                <!-- Manager -->
                <div class="col-md-6">
                  <label class="form-label">Department Manager</label>
                  <input 
                    type="text" 
                    class="form-control" 
                    v-model="form.manager"
                    :class="{ 'is-invalid': errors.manager }"
                    placeholder="e.g., John Doe"
                  >
                  <div v-if="errors.manager" class="invalid-feedback">
                    {{ errors.manager[0] }}
                  </div>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input 
                    type="email" 
                    class="form-control" 
                    v-model="form.email"
                    :class="{ 'is-invalid': errors.email }"
                    placeholder="department@company.com"
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
                    placeholder="e.g., +62 21 1234567"
                  >
                  <div v-if="errors.phone" class="invalid-feedback">
                    {{ errors.phone[0] }}
                  </div>
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
                    {{ isEdit ? 'Update Department' : 'Create Department' }}
                  </button>
                  <router-link to="/departments" class="btn btn-secondary ms-2">Cancel</router-link>
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
import departmentService from '@/services/departmentService'
import { notifyError, notifySuccess } from '@/utils/alerts'

export default {
  name: 'DepartmentForm',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const loading = ref(false)
    const errors = reactive({})
    
    const form = reactive({
      name: '',
      code: '',
      description: '',
      manager: '',
      email: '',
      phone: '',
      is_active: true
    })

    const isEdit = computed(() => !!route.params.id)

    const loadDepartment = async () => {
      if (!isEdit.value) return
      
      loading.value = true
      try {
        const { data } = await departmentService.getDepartment(route.params.id)
        form.name = data.data.name
        form.code = data.data.code
        form.description = data.data.description || ''
        form.manager = data.data.manager || ''
        form.email = data.data.email || ''
        form.phone = data.data.phone || ''
        form.is_active = data.data.is_active
      } catch (error) {
        console.error('Error loading department:', error)
        notifyError('Failed to load department data')
        router.push('/departments')
      } finally {
        loading.value = false
      }
    }

    const handleSubmit = async () => {
      loading.value = true
      Object.keys(errors).forEach(key => delete errors[key])

      try {
        if (isEdit.value) {
          await departmentService.updateDepartment(route.params.id, form)
          notifySuccess('Department updated successfully')
        } else {
          await departmentService.createDepartment(form)
          notifySuccess('Department created successfully')
        }
        router.push('/departments')
      } catch (error) {
        console.error('Error saving department:', error)
        if (error.response?.data?.errors) {
          Object.assign(errors, error.response.data.errors)
        } else {
          notifyError(error.response?.data?.message || 'Failed to save department')
        }
      } finally {
        loading.value = false
      }
    }

    onMounted(async () => {
      if (isEdit.value) {
        await loadDepartment()
      }
    })

    return {
      loading,
      form,
      errors,
      isEdit,
      handleSubmit
    }
  }
}
</script>
