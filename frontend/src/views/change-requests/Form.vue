<template>
  <div class="change-request-form">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>{{ isEdit ? 'Edit' : 'Create' }} Change Request</h2>
      <router-link to="/change-requests" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back
      </router-link>
    </div>

    <div class="card">
      <div class="card-body">
        <form @submit.prevent="handleSubmit">
          <div class="row g-3">
            <div class="col-md-12">
              <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
              <input
                type="text"
                class="form-control"
                id="title"
                v-model="form.title"
                required
                placeholder="Enter change request title"
              />
            </div>

            <div class="col-md-6">
              <label for="system_id" class="form-label">System <span class="text-danger">*</span></label>
              <Select2
                v-model="form.system_id"
                :options="systems"
                label-key="name"
                value-key="id"
                placeholder="Select System"
              />
            </div>

            <div class="col-md-3">
              <label for="change_type" class="form-label">Change Type <span class="text-danger">*</span></label>
              <Select2
                v-model="form.change_type"
                :options="changeTypeOptions"
                placeholder="Select Type"
              />
            </div>

            <div class="col-md-3">
              <label for="risk_level" class="form-label">Risk Level <span class="text-danger">*</span></label>
              <Select2
                v-model="form.risk_level"
                :options="riskLevelOptions"
                placeholder="Select Risk"
              />
            </div>

            <div class="col-md-12">
              <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
              <textarea
                class="form-control"
                id="description"
                v-model="form.description"
                rows="4"
                required
                placeholder="Describe the change in detail"
              ></textarea>
            </div>

            <div class="col-md-12">
              <label for="impact_analysis" class="form-label">Impact Analysis <span class="text-danger">*</span></label>
              <textarea
                class="form-control"
                id="impact_analysis"
                v-model="form.impact_analysis"
                rows="3"
                required
                placeholder="Describe the potential impact of this change"
              ></textarea>
            </div>

            <div class="col-md-12">
              <label for="rollback_plan" class="form-label">Rollback Plan <span class="text-danger">*</span></label>
              <textarea
                class="form-control"
                id="rollback_plan"
                v-model="form.rollback_plan"
                rows="3"
                required
                placeholder="Describe how to rollback if the change fails"
              ></textarea>
            </div>

            <div class="col-md-6">
              <label for="planned_start" class="form-label">Planned Start</label>
              <FlexDate
                v-model="form.planned_start"
                :config="dateTimeConfig"
              />
            </div>

            <div class="col-md-6">
              <label for="planned_end" class="form-label">Planned End</label>
              <FlexDate
                v-model="form.planned_end"
                :config="dateTimeConfig"
              />
            </div>

            <div class="col-md-12">
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" :disabled="loading">
                  <span v-if="loading">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Saving...
                  </span>
                  <span v-else>
                    <i class="bi bi-save me-2"></i>Save as Draft
                  </span>
                </button>
                <button
                  type="button"
                  class="btn btn-success"
                  @click="handleSubmitForApproval"
                  :disabled="loading"
                >
                  <i class="bi bi-send me-2"></i>Save & Submit for Approval
                </button>
                <router-link to="/change-requests" class="btn btn-outline-secondary">
                  Cancel
                </router-link>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import changeRequestService from '@/services/changeRequestService'
import api from '@/services/api'
import { notifyError, notifySuccess } from '@/utils/alerts'

const route = useRoute()
const router = useRouter()

const isEdit = ref(false)
const loading = ref(false)
const systems = ref([])

const form = ref({
  title: '',
  system_id: '',
  change_type: '',
  risk_level: '',
  description: '',
  impact_analysis: '',
  rollback_plan: '',
  planned_start: '',
  planned_end: ''
})

const changeTypeOptions = [
  { value: 'enhancement', label: 'Enhancement' },
  { value: 'bug_fix', label: 'Bug Fix' },
  { value: 'configuration', label: 'Configuration' },
  { value: 'emergency', label: 'Emergency' }
]

const riskLevelOptions = [
  { value: 'low', label: 'Low' },
  { value: 'medium', label: 'Medium' },
  { value: 'high', label: 'High' }
]

const dateTimeConfig = {
  enableTime: true,
  enableSeconds: true,
  time_24hr: true,
  dateFormat: 'Y-m-d H:i:S'
}

const loadSystems = async () => {
  try {
    const response = await api.get('/systems')
    if (response.data.success) {
      systems.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to load systems:', error)
  }
}

const loadChangeRequest = async (id) => {
  try {
    const response = await changeRequestService.getById(id)
    if (response.success) {
      const data = response.data
      form.value = {
        title: data.title,
        system_id: data.system.id,
        change_type: data.change_type,
        risk_level: data.risk_level,
        description: data.description,
        impact_analysis: data.impact_analysis,
        rollback_plan: data.rollback_plan,
        planned_start: data.planned_start?.substring(0, 16) || '',
        planned_end: data.planned_end?.substring(0, 16) || ''
      }
    }
  } catch (error) {
    notifyError('Failed to load change request')
    router.push('/change-requests')
  }
}

const handleSubmit = async () => {
  loading.value = true
  try {
    const data = { ...form.value }
    
    if (isEdit.value) {
      await changeRequestService.update(route.params.id, data)
      notifySuccess('Change request updated successfully')
    } else {
      await changeRequestService.create(data)
      notifySuccess('Change request created successfully')
    }
    
    router.push('/change-requests')
  } catch (error) {
    notifyError('Failed to save change request')
  } finally {
    loading.value = false
  }
}

const handleSubmitForApproval = async () => {
  loading.value = true
  try {
    let id = route.params.id
    
    if (!isEdit.value) {
      // Create first
      const response = await changeRequestService.create(form.value)
      id = response.data.id
    }
    
    // Submit for approval
    await changeRequestService.submitForApproval(id)
    notifySuccess('Change request submitted for approval successfully')
    router.push('/change-requests')
  } catch (error) {
    notifyError('Failed to submit change request')
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await loadSystems()
  
  if (route.params.id) {
    isEdit.value = true
    await loadChangeRequest(route.params.id)
  }
})
</script>
