<template>
  <div class="container-fluid py-4">
    <div class="mb-4">
      <router-link to="/access-requests" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left me-2"></i>Back to Access Requests
      </router-link>
      <h2 class="h4">Access Request Details</h2>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-danger" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <div v-else-if="request" class="row">
      <div class="col-md-8">
        <div class="card mb-4">
          <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="mb-0">{{ request.ticket_number }}</h5>
              <span class="badge" :class="getStatusClass(request.status)">
                {{ request.status.toUpperCase() }}
              </span>
            </div>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="text-muted small">Requester</label>
                <div class="fw-semibold">{{ request.user?.name }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">System</label>
                <div class="fw-semibold">{{ request.system?.name }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Access Type</label>
                <div>
                  <span class="badge" :class="getAccessTypeClass(request.access_type)">
                    {{ formatAccessType(request.access_type) }}
                  </span>
                </div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Access Level</label>
                <div>
                  <span class="badge bg-dark">{{ request.access_level.toUpperCase() }}</span>
                </div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Start Date</label>
                <div>{{ formatDate(request.start_date) }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">End Date</label>
                <div>{{ request.end_date ? formatDate(request.end_date) : 'Permanent' }}</div>
              </div>
              <div class="col-12">
                <label class="text-muted small">Purpose</label>
                <div>{{ request.purpose }}</div>
              </div>
              <div class="col-12">
                <label class="text-muted small">Business Justification</label>
                <div>{{ request.justification }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Created At</label>
                <div>{{ formatDateTime(request.created_at) }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Updated At</label>
                <div>{{ formatDateTime(request.updated_at) }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Approvals -->
        <div class="card" v-if="request.approvals && request.approvals.length > 0">
          <div class="card-header bg-white">
            <h6 class="mb-0">Approval History</h6>
          </div>
          <div class="card-body">
            <div class="list-group">
              <div 
                v-for="approval in request.approvals" 
                :key="approval.id"
                class="list-group-item"
              >
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <strong>{{ approval.approver?.name }}</strong>
                    <div class="small text-muted">Level {{ approval.level }}</div>
                  </div>
                  <span class="badge" :class="getStatusClass(approval.status)">
                    {{ approval.status.toUpperCase() }}
                  </span>
                </div>
                <div v-if="approval.comments" class="mt-2 small">
                  <strong>Comments:</strong> {{ approval.comments }}
                </div>
                <div v-if="approval.responded_at" class="small text-muted mt-1">
                  {{ formatDateTime(approval.responded_at) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card">
          <div class="card-header bg-white">
            <h6 class="mb-0">Actions</h6>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <router-link 
                v-if="request.status === 'pending' || request.status === 'rejected'"
                :to="{ name: 'EditAccessRequest', params: { id: request.id } }" 
                class="btn btn-primary"
              >
                <i class="bi bi-pencil me-2"></i>Edit Request
              </router-link>
              <button 
                v-if="request.status === 'pending'" 
                class="btn btn-danger"
                @click="confirmDelete"
              >
                <i class="bi bi-trash me-2"></i>Delete Request
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import accessRequestService from '@/services/accessRequestService'
import { confirmAction, notifyError, notifySuccess } from '@/utils/alerts'

export default {
  name: 'AccessRequestDetail',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const loading = ref(false)
    const request = ref(null)

    const getAccessTypeClass = (type) => {
      const classes = {
        new: 'bg-success',
        modify: 'bg-warning',
        revoke: 'bg-danger',
        temporary: 'bg-info'
      }
      return classes[type] || 'bg-secondary'
    }

    const getStatusClass = (status) => {
      const classes = {
        pending: 'bg-warning',
        approved: 'bg-success',
        rejected: 'bg-danger',
        implemented: 'bg-primary'
      }
      return classes[status] || 'bg-secondary'
    }

    const formatAccessType = (type) => {
      const types = {
        new: 'New Access',
        modify: 'Modify',
        revoke: 'Revoke',
        temporary: 'Temporary'
      }
      return types[type] || type
    }

    const formatDate = (date) => {
      const parsed = new Date(date)
      if (Number.isNaN(parsed.getTime())) return date
      const pad = (value) => String(value).padStart(2, '0')
      const yyyy = parsed.getFullYear()
      const mm = pad(parsed.getMonth() + 1)
      const dd = pad(parsed.getDate())
      return `${yyyy}/${mm}/${dd}`
    }

    const formatDateTime = (date) => {
      const parsed = new Date(date)
      if (Number.isNaN(parsed.getTime())) return date
      const pad = (value) => String(value).padStart(2, '0')
      const yyyy = parsed.getFullYear()
      const mm = pad(parsed.getMonth() + 1)
      const dd = pad(parsed.getDate())
      const hh = pad(parsed.getHours())
      const min = pad(parsed.getMinutes())
      const ss = pad(parsed.getSeconds())
      return `${yyyy}/${mm}/${dd} ${hh}:${min}:${ss}`
    }

    const loadAccessRequest = async () => {
      loading.value = true
      try {
        const { data } = await accessRequestService.getAccessRequest(route.params.id)
        request.value = data.data
      } catch (error) {
        console.error('Error loading access request:', error)
        notifyError('Failed to load access request')
        router.push('/access-requests')
      } finally {
        loading.value = false
      }
    }

    const confirmDelete = async () => {
      const confirmed = await confirmAction('Are you sure you want to delete this access request?')
      if (!confirmed) return

      try {
        await accessRequestService.deleteAccessRequest(route.params.id)
        notifySuccess('Access request deleted successfully')
        router.push('/access-requests')
      } catch (error) {
        console.error('Error deleting access request:', error)
        notifyError(error.response?.data?.message || 'Failed to delete access request')
      }
    }

    onMounted(() => {
      loadAccessRequest()
    })

    return {
      loading,
      request,
      getAccessTypeClass,
      getStatusClass,
      formatAccessType,
      formatDate,
      formatDateTime,
      confirmDelete
    }
  }
}
</script>
