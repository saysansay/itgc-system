<template>
  <div class="change-request-detail">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Change Request Detail</h2>
      <router-link to="/change-requests" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back
      </router-link>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <div v-else-if="changeRequest">
      <!-- Header Info -->
      <div class="card mb-3">
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <h4>{{ changeRequest.title }}</h4>
              <p class="text-muted mb-2">{{ changeRequest.ticket_number }}</p>
              <span class="badge me-2" :class="getStatusClass(changeRequest.status)">
                {{ formatStatus(changeRequest.status) }}
              </span>
              <span class="badge" :class="getRiskLevelClass(changeRequest.risk_level)">
                Risk: {{ changeRequest.risk_level }}
              </span>
            </div>
            <div class="col-md-6 text-md-end">
              <p class="mb-1"><strong>System:</strong> {{ changeRequest.system.name }}</p>
              <p class="mb-1"><strong>Type:</strong> {{ formatType(changeRequest.change_type) }}</p>
              <p class="mb-1"><strong>Requester:</strong> {{ changeRequest.requester.name }}</p>
              <p class="mb-0"><strong>Created:</strong> {{ formatDate(changeRequest.created_at) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Details -->
      <div class="row">
        <div class="col-md-8">
          <div class="card mb-3">
            <div class="card-header">
              <h5 class="mb-0">Description</h5>
            </div>
            <div class="card-body">
              <p>{{ changeRequest.description }}</p>
            </div>
          </div>

          <div class="card mb-3">
            <div class="card-header">
              <h5 class="mb-0">Impact Analysis</h5>
            </div>
            <div class="card-body">
              <p>{{ changeRequest.impact_analysis }}</p>
            </div>
          </div>

          <div class="card mb-3">
            <div class="card-header">
              <h5 class="mb-0">Rollback Plan</h5>
            </div>
            <div class="card-body">
              <p>{{ changeRequest.rollback_plan }}</p>
            </div>
          </div>

          <!-- Evidences -->
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Evidence Files</h5>
              <button class="btn btn-sm btn-primary" @click="showUploadModal = true">
                <i class="bi bi-upload me-2"></i>Upload
              </button>
            </div>
            <div class="card-body">
              <div v-if="changeRequest.evidences.length === 0" class="text-center text-muted py-3">
                No evidence files uploaded
              </div>
              <div v-else class="list-group">
                <div
                  v-for="evidence in changeRequest.evidences"
                  :key="evidence.id"
                  class="list-group-item"
                >
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <i class="bi bi-file-earmark me-2"></i>
                      <strong>{{ evidence.file_name }}</strong>
                      <div class="text-muted small">
                        {{ formatFileSize(evidence.file_size) }} - 
                        Uploaded {{ formatDate(evidence.uploaded_at) }}
                      </div>
                    </div>
                    <button class="btn btn-sm btn-outline-primary">
                      <i class="bi bi-download"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
          <div class="card mb-3">
            <div class="card-header">
              <h5 class="mb-0">Schedule</h5>
            </div>
            <div class="card-body">
              <p class="mb-2">
                <strong>Planned Start:</strong><br>
                {{ changeRequest.planned_start ? formatDateTime(changeRequest.planned_start) : 'Not set' }}
              </p>
              <p class="mb-0">
                <strong>Planned End:</strong><br>
                {{ changeRequest.planned_end ? formatDateTime(changeRequest.planned_end) : 'Not set' }}
              </p>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Team</h5>
            </div>
            <div class="card-body">
              <p class="mb-2">
                <strong>Implementer:</strong><br>
                {{ changeRequest.implementer?.name || 'Not assigned' }}
              </p>
              <p class="mb-0">
                <strong>Approved By:</strong><br>
                {{ changeRequest.approved_by?.name || 'Pending' }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Upload Modal (simplified) -->
    <div v-if="showUploadModal" class="modal-backdrop" @click="showUploadModal = false">
      <div class="modal-content" @click.stop>
        <h5>Upload Evidence</h5>
        <input type="file" @change="handleFileSelect" class="form-control mb-3" />
        <div class="d-flex gap-2">
          <button class="btn btn-primary" @click="handleUpload" :disabled="!selectedFile">
            Upload
          </button>
          <button class="btn btn-secondary" @click="showUploadModal = false">
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import changeRequestService from '@/services/changeRequestService'
import { notifyError } from '@/utils/alerts'

const route = useRoute()
const loading = ref(false)
const changeRequest = ref(null)
const showUploadModal = ref(false)
const selectedFile = ref(null)

const loadData = async () => {
  loading.value = true
  try {
    const response = await changeRequestService.getById(route.params.id)
    if (response.success) {
      changeRequest.value = response.data
    }
  } catch (error) {
    notifyError('Failed to load change request')
  } finally {
    loading.value = false
  }
}

const handleFileSelect = (event) => {
  selectedFile.value = event.target.files[0]
}

const handleUpload = async () => {
  if (!selectedFile.value) return
  
  try {
    await changeRequestService.uploadEvidence(route.params.id, selectedFile.value)
    showUploadModal.value = false
    selectedFile.value = null
    await loadData()
  } catch (error) {
    notifyError('Failed to upload file')
  }
}

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-secondary',
    pending_approval: 'bg-warning',
    approved: 'bg-info',
    rejected: 'bg-danger',
    in_progress: 'bg-primary',
    completed: 'bg-success',
    failed: 'bg-danger'
  }
  return classes[status] || 'bg-secondary'
}

const getRiskLevelClass = (level) => {
  const classes = {
    low: 'bg-success',
    medium: 'bg-warning',
    high: 'bg-danger'
  }
  return classes[level] || 'bg-secondary'
}

const formatStatus = (status) => {
  return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatType = (type) => {
  return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return dateString
  const pad = (value) => String(value).padStart(2, '0')
  const yyyy = date.getFullYear()
  const mm = pad(date.getMonth() + 1)
  const dd = pad(date.getDate())
  return `${yyyy}/${mm}/${dd}`
}

const formatDateTime = (dateString) => {
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return dateString
  const pad = (value) => String(value).padStart(2, '0')
  const yyyy = date.getFullYear()
  const mm = pad(date.getMonth() + 1)
  const dd = pad(date.getDate())
  const hh = pad(date.getHours())
  const min = pad(date.getMinutes())
  const ss = pad(date.getSeconds())
  return `${yyyy}/${mm}/${dd} ${hh}:${min}:${ss}`
}

const formatFileSize = (bytes) => {
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(2) + ' MB'
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.modal-content {
  background: white;
  padding: 20px;
  border-radius: 8px;
  min-width: 400px;
}
</style>
