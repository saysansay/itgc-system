<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 mb-0">IT Assets</h2>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-success" @click="exportToExcel">
          <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
        </button>
        <router-link to="/it-assets/create" class="btn btn-danger">
          <i class="bi bi-plus-circle me-2"></i>New Asset
        </router-link>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <input
              type="text"
              class="form-control"
              placeholder="Search tag, name, serial..."
              v-model="filters.search"
              @input="debouncedSearch"
            />
          </div>
          <div class="col-md-2">
            <Select2
              v-model="filters.category"
              :options="categoryOptions"
              placeholder="All Categories"
              @update:modelValue="loadAssets"
            />
          </div>
          <div class="col-md-2">
            <Select2
              v-model="filters.status"
              :options="statusOptions"
              placeholder="All Status"
              @update:modelValue="loadAssets"
            />
          </div>
          <div class="col-md-2">
            <Select2
              v-model="filters.assignment_status"
              :options="assignmentOptions"
              placeholder="All Assets"
              @update:modelValue="loadAssets"
            />
          </div>
          <div class="col-md-2">
            <Select2
              v-model="filters.department_id"
              :options="departments"
              label-key="name"
              value-key="id"
              placeholder="All Departments"
              @update:modelValue="loadAssets"
            />
          </div>
          <div class="col-md-1">
            <button class="btn btn-outline-secondary w-100" @click="resetFilters">
              <i class="bi bi-arrow-clockwise"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-danger" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>

        <div v-else>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Asset Tag</th>
                  <th>Name</th>
                  <th>Category</th>
                  <th>Brand/Model</th>
                  <th>Status</th>
                  <th>Assigned To</th>
                  <th>Location</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="asset in assets.data" :key="asset.id">
                  <td>
                    <span class="badge bg-dark font-monospace">{{ asset.asset_tag }}</span>
                  </td>
                  <td>{{ asset.name }}</td>
                  <td>
                    <span class="badge" :class="getCategoryClass(asset.category)">
                      {{ asset.category }}
                    </span>
                  </td>
                  <td>
                    <div v-if="asset.brand || asset.model">
                      {{ asset.brand }} {{ asset.model }}
                    </div>
                    <div v-else class="text-muted">-</div>
                  </td>
                  <td>
                    <span class="badge" :class="getStatusClass(asset.status)">
                      {{ asset.status.toUpperCase() }}
                    </span>
                  </td>
                  <td>
                    <div v-if="asset.assigned_user">
                      <i class="bi bi-person-fill me-1"></i>{{ asset.assigned_user.name }}
                    </div>
                    <div v-else class="text-muted">
                      <i class="bi bi-dash-circle me-1"></i>Unassigned
                    </div>
                  </td>
                  <td>{{ asset.location || '-' }}</td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <router-link
                        :to="{ name: 'ItAssetDetail', params: { id: asset.id } }"
                        class="btn btn-outline-primary"
                        title="View Details"
                      >
                        <i class="bi bi-eye"></i>
                      </router-link>
                      <router-link
                        :to="{ name: 'EditItAsset', params: { id: asset.id } }"
                        class="btn btn-outline-warning"
                        title="Edit"
                      >
                        <i class="bi bi-pencil"></i>
                      </router-link>
                      <button
                        class="btn btn-outline-danger"
                        @click="confirmDelete(asset.id)"
                        title="Delete"
                        :disabled="asset.assigned_to"
                      >
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="assets.data && assets.data.length === 0">
                  <td colspan="8" class="text-center text-muted py-4">
                    No IT assets found
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="assets.last_page > 1" class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted small">
              Showing {{ assets.from }} to {{ assets.to }} of {{ assets.total }} entries
            </div>
            <nav>
              <ul class="pagination pagination-sm mb-0">
                <li class="page-item" :class="{ disabled: assets.current_page === 1 }">
                  <a class="page-link" href="#" @click.prevent="changePage(assets.current_page - 1)">
                    Previous
                  </a>
                </li>
                <li
                  v-for="page in visiblePages"
                  :key="page"
                  class="page-item"
                  :class="{ active: page === assets.current_page }"
                >
                  <a class="page-link" href="#" @click.prevent="changePage(page)">
                    {{ page }}
                  </a>
                </li>
                <li class="page-item" :class="{ disabled: assets.current_page === assets.last_page }">
                  <a class="page-link" href="#" @click.prevent="changePage(assets.current_page + 1)">
                    Next
                  </a>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import itAssetService from '@/services/itAssetService'
import { confirmAction, notifyError, notifySuccess } from '@/utils/alerts'
import { downloadBlob } from '@/utils/download'

export default {
  name: 'ItAssetList',
  setup() {
    const loading = ref(false)
    const assets = ref({ data: [], current_page: 1, last_page: 1 })
    const departments = ref([])
    const filters = reactive({
      search: '',
      category: '',
      status: '',
      assignment_status: '',
      department_id: ''
    })
    const categoryOptions = [
      { value: '', label: 'All Categories' },
      { value: 'Server', label: 'Server' },
      { value: 'Laptop', label: 'Laptop' },
      { value: 'Desktop', label: 'Desktop' },
      { value: 'Network', label: 'Network' },
      { value: 'Printer', label: 'Printer' },
      { value: 'Phone', label: 'Phone' },
      { value: 'Tablet', label: 'Tablet' },
      { value: 'Storage', label: 'Storage' },
      { value: 'Other', label: 'Other' }
    ]
    const statusOptions = [
      { value: '', label: 'All Status' },
      { value: 'active', label: 'Active' },
      { value: 'repair', label: 'Repair' },
      { value: 'retired', label: 'Retired' },
      { value: 'disposed', label: 'Disposed' }
    ]
    const assignmentOptions = [
      { value: '', label: 'All Assets' },
      { value: 'assigned', label: 'Assigned' },
      { value: 'unassigned', label: 'Unassigned' }
    ]
    let searchTimeout = null

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

    const visiblePages = computed(() => {
      const current = assets.value.current_page
      const last = assets.value.last_page
      const delta = 2
      const range = []
      const rangeWithDots = []

      for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
        range.push(i)
      }

      if (current - delta > 2) {
        rangeWithDots.push(1, '...')
      } else {
        rangeWithDots.push(1)
      }

      rangeWithDots.push(...range)

      if (current + delta < last - 1) {
        rangeWithDots.push('...', last)
      } else if (last > 1) {
        rangeWithDots.push(last)
      }

      return rangeWithDots.filter(p => p !== '...' || rangeWithDots.indexOf(p) === rangeWithDots.lastIndexOf(p))
    })

    const buildParams = (extra = {}) => {
      return Object.entries({
        ...filters,
        ...extra
      })
        .filter(([, value]) => value !== '' && value !== null && value !== undefined)
        .reduce((acc, [key, value]) => {
          acc[key] = value
          return acc
        }, {})
    }

    const loadAssets = async (page = 1) => {
      loading.value = true
      try {
        const params = buildParams({ page })
        const { data } = await itAssetService.getItAssets(params)
        assets.value = data
      } catch (error) {
        console.error('Error loading IT assets:', error)
        notifyError('Failed to load IT assets')
      } finally {
        loading.value = false
      }
    }

    const loadDepartments = async () => {
      try {
        const { data } = await itAssetService.getDepartments()
        departments.value = data.data || data
      } catch (error) {
        console.error('Error loading departments:', error)
      }
    }

    const debouncedSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        loadAssets()
      }, 500)
    }

    const changePage = (page) => {
      if (page >= 1 && page <= assets.value.last_page) {
        loadAssets(page)
      }
    }

    const resetFilters = () => {
      filters.search = ''
      filters.category = ''
      filters.status = ''
      filters.assignment_status = ''
      filters.department_id = ''
      loadAssets()
    }

    const exportToExcel = async () => {
      try {
        const params = buildParams()
        const response = await itAssetService.exportItAssets(params)
        downloadBlob(response.data, 'it-assets.xlsx')
      } catch (error) {
        notifyError('Failed to export IT assets')
      }
    }

    const confirmDelete = async (id) => {
      const confirmed = await confirmAction('Are you sure you want to delete this IT asset?')
      if (!confirmed) return

      try {
        await itAssetService.deleteItAsset(id)
        notifySuccess('IT asset deleted successfully')
        loadAssets(assets.value.current_page)
      } catch (error) {
        console.error('Error deleting IT asset:', error)
        notifyError(error.response?.data?.message || 'Failed to delete IT asset')
      }
    }

    onMounted(() => {
      loadDepartments()
      loadAssets()
    })

    return {
      loading,
      assets,
      departments,
      filters,
      categoryOptions,
      statusOptions,
      assignmentOptions,
      visiblePages,
      getCategoryClass,
      getStatusClass,
      debouncedSearch,
      changePage,
      resetFilters,
      confirmDelete,
      loadAssets,
      exportToExcel
    }
  }
}
</script>
