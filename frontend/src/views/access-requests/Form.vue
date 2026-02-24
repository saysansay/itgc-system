<template>
  <div class="container-fluid py-4">
    <div class="mb-4">
      <router-link to="/access-requests" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left me-2"></i>Back to Access Requests
      </router-link>
      <h2 class="h4">{{ isEdit ? 'Edit Access Request' : 'New Access Request' }}</h2>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <form @submit.prevent="handleSubmit">
              <div class="row g-3">
                <!-- System -->
                <div class="col-12">
                  <label class="form-label">System <span class="text-danger">*</span></label>
                  <Select2
                    v-model="form.system_id"
                    :options="systems"
                    label-key="name"
                    value-key="id"
                    placeholder="- Select System -"
                    :invalid="!!errors.system_id"
                  />
                  <div v-if="errors.system_id" class="invalid-feedback">
                    {{ errors.system_id[0] }}
                  </div>
                </div>

                <!-- Access Type -->
                <div class="col-md-6">
                  <label class="form-label">Access Type <span class="text-danger">*</span></label>
                  <Select2
                    v-model="form.access_type"
                    :options="accessTypeOptions"
                    placeholder="- Select Type -"
                    :invalid="!!errors.access_type"
                  />
                  <div v-if="errors.access_type" class="invalid-feedback">
                    {{ errors.access_type[0] }}
                  </div>
                </div>

                <!-- Access Level -->
                <div class="col-md-6">
                  <label class="form-label">Access Level <span class="text-danger">*</span></label>
                  <Select2
                    v-model="form.access_level"
                    :options="accessLevelOptions"
                    placeholder="- Select Level -"
                    :invalid="!!errors.access_level"
                  />
                  <div v-if="errors.access_level" class="invalid-feedback">
                    {{ errors.access_level[0] }}
                  </div>
                </div>

                <!-- Start Date -->
                <div class="col-md-6">
                  <label class="form-label">Start Date <span class="text-danger">*</span></label>
                  <FlexDate
                    v-model="form.start_date"
                    :config="dateConfig"
                    :invalid="!!errors.start_date"
                  />
                  <div v-if="errors.start_date" class="invalid-feedback">
                    {{ errors.start_date[0] }}
                  </div>
                </div>

                <!-- End Date -->
                <div class="col-md-6">
                  <label class="form-label">End Date (Optional)</label>
                  <FlexDate
                    v-model="form.end_date"
                    :config="dateConfig"
                    :invalid="!!errors.end_date"
                  />
                  <div v-if="errors.end_date" class="invalid-feedback">
                    {{ errors.end_date[0] }}
                  </div>
                  <small class="text-muted">Leave empty for permanent access</small>
                </div>

                <!-- Purpose -->
                <div class="col-12">
                  <label class="form-label">Purpose <span class="text-danger">*</span></label>
                  <textarea 
                    class="form-control" 
                    v-model="form.purpose"
                    :class="{ 'is-invalid': errors.purpose }"
                    rows="3"
                    required
                    placeholder="Describe the purpose of this access request..."
                  ></textarea>
                  <div v-if="errors.purpose" class="invalid-feedback">
                    {{ errors.purpose[0] }}
                  </div>
                </div>

                <!-- Justification -->
                <div class="col-12">
                  <label class="form-label">Business Justification <span class="text-danger">*</span></label>
                  <textarea 
                    class="form-control" 
                    v-model="form.justification"
                    :class="{ 'is-invalid': errors.justification }"
                    rows="3"
                    required
                    placeholder="Provide business justification for this access..."
                  ></textarea>
                  <div v-if="errors.justification" class="invalid-feedback">
                    {{ errors.justification[0] }}
                  </div>
                </div>

                <!-- Submit -->
                <div class="col-12">
                  <hr>
                  <button type="submit" class="btn btn-danger" :disabled="loading">
                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                    {{ isEdit ? 'Update Request' : 'Submit Request' }}
                  </button>
                  <router-link to="/access-requests" class="btn btn-secondary ms-2">Cancel</router-link>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <h6 class="card-title">Access Request Guidelines</h6>
            <ul class="small">
              <li>All access requests require management approval</li>
              <li>Temporary access must specify end date</li>
              <li>Access will be reviewed every 90 days</li>
              <li>Provide clear business justification</li>
              <li>Access level should follow least privilege principle</li>
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
import accessRequestService from '@/services/accessRequestService'
import { notifyError, notifySuccess } from '@/utils/alerts'

export default {
  name: 'AccessRequestForm',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const loading = ref(false)
    const systems = ref([])
    const errors = reactive({})
    
    const form = reactive({
      system_id: '',
      access_type: '',
      access_level: '',
      purpose: '',
      justification: '',
      start_date: '',
      end_date: ''
    })

    const accessTypeOptions = [
      { value: 'new', label: 'New Access' },
      { value: 'modify', label: 'Modify Access' },
      { value: 'revoke', label: 'Revoke Access' },
      { value: 'temporary', label: 'Temporary Access' }
    ]

    const accessLevelOptions = [
      { value: 'read', label: 'Read Only' },
      { value: 'write', label: 'Read & Write' },
      { value: 'admin', label: 'Administrator' },
      { value: 'full', label: 'Full Control' }
    ]

    const dateConfig = {
      dateFormat: 'Y-m-d'
    }

    const isEdit = computed(() => !!route.params.id)

    const loadSystems = async () => {
      try {
        const { data } = await accessRequestService.getSystems()
        systems.value = data.data
      } catch (error) {
        console.error('Error loading systems:', error)
      }
    }

    const loadAccessRequest = async () => {
      if (!isEdit.value) return
      
      loading.value = true
      try {
        const { data } = await accessRequestService.getAccessRequest(route.params.id)
        form.system_id = data.data.system_id
        form.access_type = data.data.access_type
        form.access_level = data.data.access_level
        form.purpose = data.data.purpose
        form.justification = data.data.justification
        form.start_date = data.data.start_date
        form.end_date = data.data.end_date || ''
      } catch (error) {
        console.error('Error loading access request:', error)
        notifyError('Failed to load access request data')
        router.push('/access-requests')
      } finally {
        loading.value = false
      }
    }

    const handleSubmit = async () => {
      loading.value = true
      Object.keys(errors).forEach(key => delete errors[key])

      try {
        if (isEdit.value) {
          await accessRequestService.updateAccessRequest(route.params.id, form)
          notifySuccess('Access request updated successfully')
        } else {
          await accessRequestService.createAccessRequest(form)
          notifySuccess('Access request submitted successfully')
        }
        router.push('/access-requests')
      } catch (error) {
        console.error('Error saving access request:', error)
        if (error.response?.data?.errors) {
          Object.assign(errors, error.response.data.errors)
        } else {
          notifyError(error.response?.data?.message || 'Failed to save access request')
        }
      } finally {
        loading.value = false
      }
    }

    onMounted(async () => {
      await loadSystems()
      if (isEdit.value) {
        await loadAccessRequest()
      }
    })

    return {
      loading,
      form,
      errors,
      systems,
      isEdit,
      accessTypeOptions,
      accessLevelOptions,
      dateConfig,
      handleSubmit
    }
  }
}
</script>
