<template>
  <div class="container-fluid py-4">
    <div class="mb-4">
      <router-link to="/it-assets" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left me-2"></i>Back to IT Assets
      </router-link>
      <h2 class="h4">IT Asset Details</h2>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-danger" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <div v-else-if="asset" class="row">
      <div class="col-md-8">
        <!-- Asset Information -->
        <div class="card mb-4">
          <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="mb-0">{{ asset.name }}</h5>
              <div>
                <span class="badge bg-dark me-2">{{ asset.asset_tag }}</span>
                <span class="badge" :class="getStatusClass(asset.status)">
                  {{ asset.status.toUpperCase() }}
                </span>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="text-muted small">Category</label>
                <div>
                  <span class="badge" :class="getCategoryClass(asset.category)">
                    {{ asset.category }}
                  </span>
                </div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Status</label>
                <div>
                  <span class="badge" :class="getStatusClass(asset.status)">
                    {{ asset.status.toUpperCase() }}
                  </span>
                </div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Brand</label>
                <div>{{ asset.brand || '-' }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Model</label>
                <div>{{ asset.model || '-' }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Serial Number</label>
                <div class="font-monospace">{{ asset.serial_number || '-' }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Currently Assigned To</label>
                <div v-if="asset.assigned_user" class="fw-semibold">
                  <i class="bi bi-person-fill me-1"></i>{{ asset.assigned_user.name }}
                </div>
                <div v-else class="text-muted">
                  <i class="bi bi-dash-circle me-1"></i>Unassigned
                </div>
              </div>
              <div class="col-12" v-if="asset.specifications">
                <label class="text-muted small">Specifications</label>
                <div>{{ asset.specifications }}</div>
              </div>
              <div class="col-md-4">
                <label class="text-muted small">Purchase Date</label>
                <div>{{ asset.purchase_date ? formatDate(asset.purchase_date) : '-' }}</div>
              </div>
              <div class="col-md-4">
                <label class="text-muted small">Purchase Price</label>
                <div>{{ asset.purchase_price ? formatCurrency(asset.purchase_price) : '-' }}</div>
              </div>
              <div class="col-md-4">
                <label class="text-muted small">Warranty Expiry</label>
                <div>{{ asset.warranty_expiry || '-' }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Department</label>
                <div>{{ asset.department?.name || '-' }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Location</label>
                <div>{{ asset.location || '-' }}</div>
              </div>
              <div class="col-12" v-if="asset.notes">
                <label class="text-muted small">Notes</label>
                <div>{{ asset.notes }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Assignment History -->
        <div class="card">
          <div class="card-header bg-white">
            <h6 class="mb-0">Assignment History</h6>
          </div>
          <div class="card-body">
            <div v-if="asset.assignments && asset.assignments.length > 0">
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>User</th>
                      <th>Assigned</th>
                      <th>Returned</th>
                      <th>Condition</th>
                      <th>Notes</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="assignment in asset.assignments" :key="assignment.id">
                      <td>{{ assignment.user?.name }}</td>
                      <td>{{ formatDate(assignment.assigned_date) }}</td>
                      <td>
                        <span v-if="assignment.returned_date">
                          {{ formatDate(assignment.returned_date) }}
                        </span>
                        <span v-else class="badge bg-success">Current</span>
                      </td>
                      <td>
                        <div>
                          <small>Assign: </small>
                          <span class="badge" :class="getConditionClass(assignment.condition_on_assign)">
                            {{ assignment.condition_on_assign }}
                          </span>
                        </div>
                        <div v-if="assignment.condition_on_return">
                          <small>Return: </small>
                          <span class="badge" :class="getConditionClass(assignment.condition_on_return)">
                            {{ assignment.condition_on_return }}
                          </span>
                        </div>
                      </td>
                      <td>
                        <div v-if="assignment.assignment_notes" class="small">
                          {{ truncate(assignment.assignment_notes, 50) }}
                        </div>
                        <div v-if="assignment.return_notes" class="small text-muted">
                          <em>Return: {{ truncate(assignment.return_notes, 50) }}</em>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div v-else class="text-center text-muted py-3">
              <i class="bi bi-inbox"></i> No assignment history
            </div>
          </div>
        </div>
      </div>

      <!-- Actions Sidebar -->
      <div class="col-md-4">
        <div class="card mb-3">
          <div class="card-header bg-white">
            <h6 class="mb-0">Actions</h6>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <router-link
                :to="{ name: 'EditItAsset', params: { id: asset.id } }"
                class="btn btn-primary"
              >
                <i class="bi bi-pencil me-2"></i>Edit Asset
              </router-link>
              <button
                v-if="!asset.assigned_to"
                class="btn btn-success"
                @click="showAssignModal = true"
              >
                <i class="bi bi-person-plus me-2"></i>Assign Asset
              </button>
              <button
                v-else
                class="btn btn-warning"
                @click="showReturnModal = true"
              >
                <i class="bi bi-box-arrow-left me-2"></i>Return Asset
              </button>
              <button
                class="btn btn-danger"
                @click="confirmDelete"
                :disabled="asset.assigned_to"
              >
                <i class="bi bi-trash me-2"></i>Delete Asset
              </button>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header bg-white">
            <h6 class="mb-0">Asset Status</h6>
          </div>
          <div class="card-body">
            <ul class="list-unstyled small mb-0">
              <li class="mb-2">
                <strong>Active:</strong> Asset is operational and available
              </li>
              <li class="mb-2">
                <strong>Repair:</strong> Asset is under maintenance
              </li>
              <li class="mb-2">
                <strong>Retired:</strong> Asset is no longer in use
              </li>
              <li class="mb-2">
                <strong>Disposed:</strong> Asset has been permanently removed
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Assign Modal -->
    <div v-if="showAssignModal" class="modal d-block" style="background: rgba(0,0,0,0.5)">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Assign Asset</h5>
            <button type="button" class="btn-close" @click="showAssignModal = false"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="handleAssign">
              <div class="mb-3">
                <label class="form-label">User <span class="text-danger">*</span></label>
                <Select2
                  v-model="assignForm.user_id"
                  :options="users"
                  label-key="name"
                  value-key="id"
                  placeholder="Select User"
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Assigned Date <span class="text-danger">*</span></label>
                <FlexDate
                  v-model="assignForm.assigned_date"
                  :config="dateConfig"
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Condition <span class="text-danger">*</span></label>
                <Select2
                  v-model="assignForm.condition_on_assign"
                  :options="conditionOptions"
                  placeholder="Select Condition"
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Assignment Notes</label>
                <textarea
                  class="form-control"
                  v-model="assignForm.assignment_notes"
                  rows="3"
                  placeholder="Add any notes about this assignment..."
                ></textarea>
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" @click="showAssignModal = false">
                  Cancel
                </button>
                <button type="submit" class="btn btn-success" :disabled="assigning">
                  <span v-if="assigning" class="spinner-border spinner-border-sm me-2"></span>
                  Assign Asset
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Return Modal -->
    <div v-if="showReturnModal" class="modal d-block" style="background: rgba(0,0,0,0.5)">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Return Asset</h5>
            <button type="button" class="btn-close" @click="showReturnModal = false"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="handleReturn">
              <div class="alert alert-info">
                <strong>Returning from:</strong> {{ asset?.assigned_user?.name }}
              </div>
              <div class="mb-3">
                <label class="form-label">Return Date <span class="text-danger">*</span></label>
                <FlexDate
                  v-model="returnForm.returned_date"
                  :config="dateConfig"
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Condition <span class="text-danger">*</span></label>
                <Select2
                  v-model="returnForm.condition_on_return"
                  :options="conditionOptions"
                  placeholder="Select Condition"
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Return Notes</label>
                <textarea
                  class="form-control"
                  v-model="returnForm.return_notes"
                  rows="3"
                  placeholder="Add any notes about the return condition..."
                ></textarea>
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" @click="showReturnModal = false">
                  Cancel
                </button>
                <button type="submit" class="btn btn-warning" :disabled="returning">
                  <span v-if="returning" class="spinner-border spinner-border-sm me-2"></span>
                  Return Asset
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import itAssetService from '@/services/itAssetService'
import { confirmAction, notifyError, notifySuccess } from '@/utils/alerts'

export default {
  name: 'ItAssetDetail',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const loading = ref(false)
    const assigning = ref(false)
    const returning = ref(false)
    const asset = ref(null)
    const users = ref([])
    const showAssignModal = ref(false)
    const showReturnModal = ref(false)

    const assignForm = reactive({
      user_id: '',
      assigned_date: new Date().toISOString().split('T')[0],
      condition_on_assign: 'good',
      assignment_notes: ''
    })

    const returnForm = reactive({
      returned_date: new Date().toISOString().split('T')[0],
      condition_on_return: 'good',
      return_notes: ''
    })

    const conditionOptions = [
      { value: 'excellent', label: 'Excellent' },
      { value: 'good', label: 'Good' },
      { value: 'fair', label: 'Fair' },
      { value: 'poor', label: 'Poor' }
    ]

    const dateConfig = {
      dateFormat: 'Y-m-d'
    }

    const getCategoryClass = (category) => {
      const classes = {
        Server: 'bg-primary',
        Laptop: 'bg-info',
        Desktop: 'bg-secondary',
        Network: 'bg-success',
        Printer: 'bg-warning',
        Phone: 'bg-info',
        Tablet: 'bg-info',
        Storage: 'bg-dark',
        Other: 'bg-secondary'
      }
      return classes[category] || 'bg-secondary'
    }

    const getStatusClass = (status) => {
      const classes = {
        active: 'bg-success',
        repair: 'bg-warning',
        retired: 'bg-secondary',
        disposed: 'bg-danger'
      }
      return classes[status] || 'bg-secondary'
    }

    const getConditionClass = (condition) => {
      const classes = {
        excellent: 'bg-success',
        good: 'bg-primary',
        fair: 'bg-warning',
        poor: 'bg-danger'
      }
      return classes[condition] || 'bg-secondary'
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

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(amount)
    }

    const truncate = (text, length) => {
      if (!text) return ''
      return text.length > length ? text.substring(0, length) + '...' : text
    }

    const loadAsset = async () => {
      loading.value = true
      try {
        const { data } = await itAssetService.getItAsset(route.params.id)
        asset.value = data.data
      } catch (error) {
        console.error('Error loading asset:', error)
        notifyError('Failed to load asset')
        router.push('/it-assets')
      } finally {
        loading.value = false
      }
    }

    const loadUsers = async () => {
      try {
        const { data } = await itAssetService.getUsers()
        users.value = data.data
      } catch (error) {
        console.error('Error loading users:', error)
      }
    }

    const handleAssign = async () => {
      assigning.value = true
      try {
        await itAssetService.assignAsset(route.params.id, assignForm)
        notifySuccess('Asset assigned successfully')
        showAssignModal.value = false
        loadAsset()
      } catch (error) {
        console.error('Error assigning asset:', error)
        notifyError(error.response?.data?.message || 'Failed to assign asset')
      } finally {
        assigning.value = false
      }
    }

    const handleReturn = async () => {
      returning.value = true
      try {
        await itAssetService.returnAsset(route.params.id, returnForm)
        notifySuccess('Asset returned successfully')
        showReturnModal.value = false
        loadAsset()
      } catch (error) {
        console.error('Error returning asset:', error)
        notifyError(error.response?.data?.message || 'Failed to return asset')
      } finally {
        returning.value = false
      }
    }

    const confirmDelete = async () => {
      const confirmed = await confirmAction('Are you sure you want to delete this asset?')
      if (!confirmed) return

      try {
        await itAssetService.deleteItAsset(route.params.id)
        notifySuccess('Asset deleted successfully')
        router.push('/it-assets')
      } catch (error) {
        console.error('Error deleting asset:', error)
        notifyError(error.response?.data?.message || 'Failed to delete asset')
      }
    }

    onMounted(() => {
      loadAsset()
      loadUsers()
    })

    return {
      loading,
      assigning,
      returning,
      asset,
      users,
      showAssignModal,
      showReturnModal,
      assignForm,
      returnForm,
      conditionOptions,
      dateConfig,
      getCategoryClass,
      getStatusClass,
      getConditionClass,
      formatDate,
      formatCurrency,
      truncate,
      handleAssign,
      handleReturn,
      confirmDelete
    }
  }
}
</script>
