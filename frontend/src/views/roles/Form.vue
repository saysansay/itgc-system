<template>
  <div class="container-fluid py-4">
    <div class="mb-4">
      <router-link to="/roles" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left me-2"></i>Back to Roles
      </router-link>
      <h2 class="h4">{{ isEdit ? 'Edit Role' : 'Create Role' }}</h2>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <form @submit.prevent="handleSubmit">
              <div class="row g-3">
                <!-- Role Name -->
                <div class="col-12">
                  <label class="form-label">Role Name <span class="text-danger">*</span></label>
                  <input 
                    type="text" 
                    class="form-control" 
                    v-model="form.name"
                    :class="{ 'is-invalid': errors.name }"
                    required
                    placeholder="e.g., IT Manager, Auditor, etc."
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
                    placeholder="Describe the role's responsibilities..."
                  ></textarea>
                  <div v-if="errors.description" class="invalid-feedback">
                    {{ errors.description[0] }}
                  </div>
                </div>

                <!-- Permissions -->
                <div class="col-12">
                  <hr>
                  <h6>Permissions</h6>
                  <p class="text-muted small">Select the permissions for this role</p>
                  
                  <div v-if="loadingPermissions" class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-danger"></div>
                    <span class="ms-2">Loading permissions...</span>
                  </div>

                  <div v-else class="permissions-container">
                    <div v-for="(permissions, module) in groupedPermissions" :key="module" class="mb-4">
                      <div class="permission-module">
                        <h6 class="text-danger mb-3">
                          <i class="bi bi-folder me-2"></i>{{ module }}
                        </h6>
                        <div class="row">
                          <div v-for="permission in permissions" :key="permission.id" class="col-md-6 mb-2">
                            <div class="form-check">
                              <input 
                                class="form-check-input" 
                                type="checkbox" 
                                :id="`permission-${permission.id}`"
                                :value="permission.id"
                                v-model="form.permission_ids"
                              >
                              <label class="form-check-label" :for="`permission-${permission.id}`">
                                <strong>{{ permission.name }}</strong>
                                <small class="text-muted d-block">{{ permission.description }}</small>
                              </label>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div v-if="errors.permission_ids" class="text-danger small mt-1">
                    {{ errors.permission_ids[0] }}
                  </div>
                </div>

                <!-- Submit -->
                <div class="col-12">
                  <hr>
                  <button type="submit" class="btn btn-danger" :disabled="loading">
                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                    {{ isEdit ? 'Update Role' : 'Create Role' }}
                  </button>
                  <router-link to="/roles" class="btn btn-secondary ms-2">Cancel</router-link>
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
import roleService from '@/services/roleService'
import { notifyError, notifySuccess } from '@/utils/alerts'

export default {
  name: 'RoleForm',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const loading = ref(false)
    const loadingPermissions = ref(false)
    const groupedPermissions = ref({})
    const errors = reactive({})
    
    const form = reactive({
      name: '',
      description: '',
      permission_ids: []
    })

    const isEdit = computed(() => !!route.params.id)

    const loadPermissions = async () => {
      loadingPermissions.value = true
      try {
        const { data } = await roleService.getPermissions()
        groupedPermissions.value = data
      } catch (error) {
        console.error('Error loading permissions:', error)
        notifyError('Failed to load permissions')
      } finally {
        loadingPermissions.value = false
      }
    }

    const loadRole = async () => {
      if (!isEdit.value) return
      
      loading.value = true
      try {
        const { data } = await roleService.getRole(route.params.id)
        form.name = data.data.name
        form.description = data.data.description || ''
        form.permission_ids = data.data.permissions.map(p => p.id)
      } catch (error) {
        console.error('Error loading role:', error)
        notifyError('Failed to load role data')
        router.push('/roles')
      } finally {
        loading.value = false
      }
    }

    const handleSubmit = async () => {
      loading.value = true
      Object.keys(errors).forEach(key => delete errors[key])

      try {
        if (isEdit.value) {
          await roleService.updateRole(route.params.id, form)
          notifySuccess('Role updated successfully')
        } else {
          await roleService.createRole(form)
          notifySuccess('Role created successfully')
        }
        router.push('/roles')
      } catch (error) {
        console.error('Error saving role:', error)
        if (error.response?.data?.errors) {
          Object.assign(errors, error.response.data.errors)
        } else {
          notifyError(error.response?.data?.message || 'Failed to save role')
        }
      } finally {
        loading.value = false
      }
    }

    onMounted(async () => {
      await loadPermissions()
      if (isEdit.value) {
        await loadRole()
      }
    })

    return {
      loading,
      loadingPermissions,
      form,
      errors,
      groupedPermissions,
      isEdit,
      handleSubmit
    }
  }
}
</script>

<style scoped>
.permissions-container {
  max-height: 600px;
  overflow-y: auto;
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
  padding: 1rem;
}

.permission-module {
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 0.375rem;
  margin-bottom: 1rem;
}

.permission-module:last-child {
  margin-bottom: 0;
}
</style>
