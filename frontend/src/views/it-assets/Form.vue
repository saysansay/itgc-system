<template>
  <div class="container-fluid py-4">
    <div class="mb-4">
      <router-link to="/it-assets" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left me-2"></i>Back to IT Assets
      </router-link>
      <h2 class="h4">{{ isEdit ? 'Edit' : 'Create' }} IT Asset</h2>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <form @submit.prevent="handleSubmit">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Asset Tag <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="formData.asset_tag"
                    :class="{ 'is-invalid': errors.asset_tag }"
                    placeholder="AST-2024-001"
                    required
                  />
                  <div v-if="errors.asset_tag" class="invalid-feedback">
                    {{ errors.asset_tag[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Asset Name <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="formData.name"
                    :class="{ 'is-invalid': errors.name }"
                    placeholder="Dell Latitude 5420"
                    required
                  />
                  <div v-if="errors.name" class="invalid-feedback">
                    {{ errors.name[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Category <span class="text-danger">*</span></label>
                  <Select2
                    v-model="formData.category"
                    :options="categoryOptions"
                    placeholder="Select Category"
                    :invalid="!!errors.category"
                  />
                  <div v-if="errors.category" class="invalid-feedback">
                    {{ errors.category[0] }}
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Status</label>
                  <Select2
                    v-model="formData.status"
                    :options="statusOptions"
                    placeholder="Select Status"
                  />
                </div>

                <div class="col-md-4">
                  <label class="form-label">Brand</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="formData.brand"
                    placeholder="Dell"
                  />
                </div>

                <div class="col-md-4">
                  <label class="form-label">Model</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="formData.model"
                    placeholder="Latitude 5420"
                  />
                </div>

                <div class="col-md-4">
                  <label class="form-label">Serial Number</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="formData.serial_number"
                    placeholder="SN123456789"
                  />
                </div>

                <div class="col-12">
                  <label class="form-label">Specifications</label>
                  <textarea
                    class="form-control"
                    v-model="formData.specifications"
                    rows="3"
                    placeholder="Intel Core i5-11500, 16GB RAM, 512GB SSD, Windows 11 Pro"
                  ></textarea>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Purchase Date</label>
                  <FlexDate
                    v-model="formData.purchase_date"
                    :config="dateConfig"
                  />
                </div>

                <div class="col-md-4">
                  <label class="form-label">Purchase Price</label>
                  <input
                    type="number"
                    class="form-control"
                    v-model="formData.purchase_price"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                  />
                </div>

                <div class="col-md-4">
                  <label class="form-label">Warranty Expiry</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="formData.warranty_expiry"
                    placeholder="2025-12-31 or 3 years"
                  />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Department</label>
                  <Select2
                    v-model="formData.department_id"
                    :options="departmentOptions"
                    placeholder="No Department"
                  />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Location</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="formData.location"
                    placeholder="Building A, Floor 2, Room 201"
                  />
                </div>

                <div class="col-12">
                  <label class="form-label">Notes</label>
                  <textarea
                    class="form-control"
                    v-model="formData.notes"
                    rows="3"
                    placeholder="Additional notes or remarks..."
                  ></textarea>
                </div>
              </div>

              <div class="mt-4">
                <button type="submit" class="btn btn-danger me-2" :disabled="submitting">
                  <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                  {{ isEdit ? 'Update' : 'Create' }} Asset
                </button>
                <router-link to="/it-assets" class="btn btn-outline-secondary">
                  Cancel
                </router-link>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card">
          <div class="card-header bg-white">
            <h6 class="mb-0">Asset Management</h6>
          </div>
          <div class="card-body">
            <ul class="small mb-0">
              <li><strong>Asset Tag:</strong> Unique identifier for each asset</li>
              <li><strong>Categories:</strong> Group assets by type for better organization</li>
              <li><strong>Status:</strong>
                <ul class="mt-1">
                  <li><strong>Active:</strong> In use</li>
                  <li><strong>Repair:</strong> Under maintenance</li>
                  <li><strong>Retired:</strong> No longer in use</li>
                  <li><strong>Disposed:</strong> Permanently removed</li>
                </ul>
              </li>
              <li class="mt-2">Keep serial numbers and purchase info for warranty claims</li>
              <li>Update location when assets are moved</li>
              <li>Assign assets to users from the detail page</li>
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
import itAssetService from '@/services/itAssetService'
import { notifyError, notifySuccess } from '@/utils/alerts'

export default {
  name: 'ItAssetForm',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const submitting = ref(false)
    const departments = ref([])
    const errors = reactive({})
    const formData = reactive({
      asset_tag: '',
      name: '',
      category: '',
      brand: '',
      model: '',
      serial_number: '',
      specifications: '',
      purchase_date: '',
      purchase_price: null,
      warranty_expiry: '',
      status: 'active',
      department_id: '',
      location: '',
      notes: ''
    })

    const categoryOptions = [
      { value: 'Server', label: 'Server' },
      { value: 'Laptop', label: 'Laptop' },
      { value: 'Desktop', label: 'Desktop' },
      { value: 'Network', label: 'Network Equipment' },
      { value: 'Printer', label: 'Printer' },
      { value: 'Phone', label: 'Phone' },
      { value: 'Tablet', label: 'Tablet' },
      { value: 'Storage', label: 'Storage' },
      { value: 'Other', label: 'Other' }
    ]

    const statusOptions = [
      { value: 'active', label: 'Active' },
      { value: 'repair', label: 'Repair' },
      { value: 'retired', label: 'Retired' },
      { value: 'disposed', label: 'Disposed' }
    ]

    const dateConfig = {
      dateFormat: 'Y-m-d'
    }

    const departmentOptions = computed(() => [
      { value: '', label: 'No Department' },
      ...departments.value.map((dept) => ({ value: dept.id, label: dept.name }))
    ])

    const isEdit = computed(() => !!route.params.id)

    const loadDepartments = async () => {
      try {
        const { data } = await itAssetService.getDepartments()
        departments.value = data.data || data
      } catch (error) {
        console.error('Error loading departments:', error)
      }
    }

    const loadAsset = async () => {
      try {
        const { data } = await itAssetService.getItAsset(route.params.id)
        const asset = data.data
        formData.asset_tag = asset.asset_tag
        formData.name = asset.name
        formData.category = asset.category
        formData.brand = asset.brand || ''
        formData.model = asset.model || ''
        formData.serial_number = asset.serial_number || ''
        formData.specifications = asset.specifications || ''
        formData.purchase_date = asset.purchase_date || ''
        formData.purchase_price = asset.purchase_price
        formData.warranty_expiry = asset.warranty_expiry || ''
        formData.status = asset.status
        formData.department_id = asset.department_id || ''
        formData.location = asset.location || ''
        formData.notes = asset.notes || ''
      } catch (error) {
        notifyError('Failed to load asset')
        router.push('/it-assets')
      }
    }

    const handleSubmit = async () => {
      submitting.value = true
      Object.keys(errors).forEach(key => delete errors[key])

      try {
        if (isEdit.value) {
          await itAssetService.updateItAsset(route.params.id, formData)
          notifySuccess('IT asset updated successfully')
        } else {
          await itAssetService.createItAsset(formData)
          notifySuccess('IT asset created successfully')
        }
        router.push('/it-assets')
      } catch (error) {
        if (error.response?.data?.errors) {
          Object.assign(errors, error.response.data.errors)
        } else {
          notifyError(error.response?.data?.message || 'Failed to save IT asset')
        }
      } finally {
        submitting.value = false
      }
    }

    onMounted(() => {
      loadDepartments()
      if (isEdit.value) {
        loadAsset()
      }
    })

    return {
      formData,
      errors,
      submitting,
      departments,
      isEdit,
      categoryOptions,
      statusOptions,
      dateConfig,
      departmentOptions,
      handleSubmit
    }
  }
}
</script>
