<template>
  <div class="container-fluid py-4">
    <div class="mb-4">
      <router-link to="/systems" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left me-2"></i>Back to Systems
      </router-link>
      <h2 class="h4">{{ isEdit ? 'Edit System' : 'Create System' }}</h2>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <form @submit.prevent="handleSubmit">
              <div class="row g-3">
                <!-- System Code -->
                <div class="col-md-6">
                  <label class="form-label">System Code <span class="text-danger">*</span></label>
                  <input 
                    type="text" 
                    class="form-control" 
                    v-model="form.code"
                    :class="{ 'is-invalid': errors.code }"
                    required
                    placeholder="e.g., SAP01, CRM01"
                  >
                  <div v-if="errors.code" class="invalid-feedback">
                    {{ errors.code[0] }}
                  </div>
                </div>

                <!-- System Name -->
                <div class="col-md-6">
                  <label class="form-label">System Name <span class="text-danger">*</span></label>
                  <input 
                    type="text" 
                    class="form-control" 
                    v-model="form.name"
                    :class="{ 'is-invalid': errors.name }"
                    required
                    placeholder="e.g., SAP ERP, Salesforce CRM"
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
                    placeholder="Describe the system's purpose and functionality..."
                  ></textarea>
                  <div v-if="errors.description" class="invalid-feedback">
                    {{ errors.description[0] }}
                  </div>
                </div>

                <!-- Category -->
                <div class="col-md-6">
                  <label class="form-label">Category <span class="text-danger">*</span></label>
                  <Select2
                    v-model="form.category"
                    :options="categoryOptions"
                    placeholder="- Select Category -"
                    :invalid="!!errors.category"
                  />
                  <div v-if="errors.category" class="invalid-feedback">
                    {{ errors.category[0] }}
                  </div>
                </div>

                <!-- Version -->
                <div class="col-md-6">
                  <label class="form-label">Version</label>
                  <input 
                    type="text" 
                    class="form-control" 
                    v-model="form.version"
                    :class="{ 'is-invalid': errors.version }"
                    placeholder="e.g., v2.1.0, 2024"
                  >
                  <div v-if="errors.version" class="invalid-feedback">
                    {{ errors.version[0] }}
                  </div>
                </div>

                <!-- Vendor -->
                <div class="col-md-6">
                  <label class="form-label">Vendor</label>
                  <input 
                    type="text" 
                    class="form-control" 
                    v-model="form.vendor"
                    :class="{ 'is-invalid': errors.vendor }"
                    placeholder="e.g., SAP, Oracle, Microsoft"
                  >
                  <div v-if="errors.vendor" class="invalid-feedback">
                    {{ errors.vendor[0] }}
                  </div>
                </div>

                <!-- Owner -->
                <div class="col-md-6">
                  <label class="form-label">System Owner</label>
                  <input 
                    type="text" 
                    class="form-control" 
                    v-model="form.owner"
                    :class="{ 'is-invalid': errors.owner }"
                    placeholder="e.g., Finance Department, IT Team"
                  >
                  <div v-if="errors.owner" class="invalid-feedback">
                    {{ errors.owner[0] }}
                  </div>
                </div>

                <!-- URL -->
                <div class="col-12">
                  <label class="form-label">System URL</label>
                  <input 
                    type="url" 
                    class="form-control" 
                    v-model="form.url"
                    :class="{ 'is-invalid': errors.url }"
                    placeholder="https://system.company.com"
                  >
                  <div v-if="errors.url" class="invalid-feedback">
                    {{ errors.url[0] }}
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
                    {{ isEdit ? 'Update System' : 'Create System' }}
                  </button>
                  <router-link to="/systems" class="btn btn-secondary ms-2">Cancel</router-link>
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
import systemService from '@/services/systemService'
import { notifyError, notifySuccess } from '@/utils/alerts'

export default {
  name: 'SystemForm',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const loading = ref(false)
    const errors = reactive({})
    
    const form = reactive({
      name: '',
      code: '',
      description: '',
      category: '',
      version: '',
      vendor: '',
      owner: '',
      url: '',
      is_active: true
    })

    const categoryOptions = [
      { value: 'erp', label: 'ERP (Enterprise Resource Planning)' },
      { value: 'crm', label: 'CRM (Customer Relationship Management)' },
      { value: 'hrms', label: 'HRMS (Human Resource Management)' },
      { value: 'financial', label: 'Financial System' },
      { value: 'inventory', label: 'Inventory Management' },
      { value: 'other', label: 'Other' }
    ]

    const isEdit = computed(() => !!route.params.id)

    const loadSystem = async () => {
      if (!isEdit.value) return
      
      loading.value = true
      try {
        const { data } = await systemService.getSystem(route.params.id)
        form.name = data.data.name
        form.code = data.data.code
        form.description = data.data.description || ''
        form.category = data.data.category
        form.version = data.data.version || ''
        form.vendor = data.data.vendor || ''
        form.owner = data.data.owner || ''
        form.url = data.data.url || ''
        form.is_active = data.data.is_active
      } catch (error) {
        console.error('Error loading system:', error)
        notifyError('Failed to load system data')
        router.push('/systems')
      } finally {
        loading.value = false
      }
    }

    const handleSubmit = async () => {
      loading.value = true
      Object.keys(errors).forEach(key => delete errors[key])

      try {
        if (isEdit.value) {
          await systemService.updateSystem(route.params.id, form)
          notifySuccess('System updated successfully')
        } else {
          await systemService.createSystem(form)
          notifySuccess('System created successfully')
        }
        router.push('/systems')
      } catch (error) {
        console.error('Error saving system:', error)
        if (error.response?.data?.errors) {
          Object.assign(errors, error.response.data.errors)
        } else {
          notifyError(error.response?.data?.message || 'Failed to save system')
        }
      } finally {
        loading.value = false
      }
    }

    onMounted(async () => {
      if (isEdit.value) {
        await loadSystem()
      }
    })

    return {
      loading,
      form,
      errors,
      isEdit,
      categoryOptions,
      handleSubmit
    }
  }
}
</script>
