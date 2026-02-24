<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <router-link to="/general-troubles" class="btn btn-outline-secondary btn-sm mb-3">
          <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar
        </router-link>
        <h2 class="h4 mb-1">Detail General Trouble</h2>
        <div class="text-muted">{{ item?.trouble_number || '-' }}</div>
      </div>
      <div class="d-flex gap-2">
        <router-link
          v-if="item"
          :to="{ name: 'EditGeneralTrouble', params: { id: item.id } }"
          class="btn btn-outline-primary"
        >
          <i class="bi bi-pencil me-2"></i>Edit
        </router-link>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Tanggal</label>
                <div>{{ formatDateTime(item?.reported_at) }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">User</label>
                <div>{{ item?.user?.name || '-' }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">PIC</label>
                <div>{{ item?.pic?.name || '-' }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Partner</label>
                <div>{{ item?.partner || '-' }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Type</label>
                <div>{{ formatType(item?.type) }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Durasi</label>
                <div>{{ formatDuration(item?.duration_value, item?.duration_unit) }}</div>
              </div>
              <div class="col-12">
                <label class="form-label">Problem</label>
                <div>{{ item?.problem || '-' }}</div>
              </div>
              <div class="col-12">
                <label class="form-label">Analisa</label>
                <div>{{ item?.analysis || '-' }}</div>
              </div>
              <div class="col-12">
                <label class="form-label">Solusi</label>
                <div>{{ item?.solution || '-' }}</div>
              </div>
              <div class="col-12">
                <label class="form-label">Keterangan</label>
                <div>{{ item?.notes || '-' }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card">
          <div class="card-header bg-white">
            <h6 class="mb-0">Status</h6>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">Status</label>
              <div>
                <span class="badge" :class="getStatusClass(item?.status)">
                  {{ formatStatus(item?.status) }}
                </span>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Created At</label>
              <div>{{ formatDateTime(item?.created_at) }}</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Updated At</label>
              <div>{{ formatDateTime(item?.updated_at) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import generalTroubleService from '@/services/generalTroubleService'
import { notifyError } from '@/utils/alerts'

export default {
  name: 'GeneralTroubleDetail',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const item = ref(null)

    const formatType = (value) => {
      const map = {
        hardware: 'Hardware',
        software: 'Software',
        network: 'Network',
        security: 'Security',
        others: 'Others'
      }
      return map[value] || '-'
    }

    const formatStatus = (status) => {
      if (!status) return '-'
      return status.replace('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase())
    }

    const getStatusClass = (status) => {
      const classes = {
        open: 'bg-warning',
        on_progress: 'bg-primary',
        done: 'bg-success',
        closed: 'bg-secondary'
      }
      return classes[status] || 'bg-secondary'
    }

    const formatDuration = (value, unit) => {
      if (!value || !unit) return '-'
      const unitLabel = unit === 'minute' ? 'Menit' : 'Jam'
      return `${value} ${unitLabel}`
    }

    const formatDateTime = (datetime) => {
      if (!datetime) return '-'
      const parsed = new Date(datetime)
      if (Number.isNaN(parsed.getTime())) return datetime
      const pad = (value) => String(value).padStart(2, '0')
      const yyyy = parsed.getFullYear()
      const mm = pad(parsed.getMonth() + 1)
      const dd = pad(parsed.getDate())
      const hh = pad(parsed.getHours())
      const min = pad(parsed.getMinutes())
      const ss = pad(parsed.getSeconds())
      return `${yyyy}/${mm}/${dd} ${hh}:${min}:${ss}`
    }

    const loadItem = async () => {
      try {
        const { data } = await generalTroubleService.getGeneralTrouble(route.params.id)
        item.value = data.data
      } catch (error) {
        console.error('Error loading general trouble:', error)
        notifyError('Gagal memuat data general trouble')
        router.push('/general-troubles')
      }
    }

    onMounted(() => {
      loadItem()
    })

    return {
      item,
      formatType,
      formatStatus,
      getStatusClass,
      formatDuration,
      formatDateTime
    }
  }
}
</script>
