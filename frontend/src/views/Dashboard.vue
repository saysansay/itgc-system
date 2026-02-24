<template>
  <div class="dashboard">
    <div class="oracle-summary mb-4">
      <div>
        <div class="oracle-summary-kicker">ITGC Overview</div>
        <h2 class="mb-1">Dashboard</h2>
        <div class="text-muted small">Ringkasan aktivitas dan perubahan terbaru</div>
      </div>
      <span class="badge bg-light oracle-summary-badge">{{ currentYear }}</span>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="oracle-kpi-card">
          <div class="oracle-kpi-icon">
            <i class="bi bi-folder-open"></i>
          </div>
          <div>
            <div class="text-muted small">Open Requests</div>
            <h4 class="mb-0">{{ stats.open_requests }}</h4>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="oracle-kpi-card">
          <div class="oracle-kpi-icon">
            <i class="bi bi-clock-history"></i>
          </div>
          <div>
            <div class="text-muted small">Pending Approvals</div>
            <h4 class="mb-0">{{ stats.pending_approvals }}</h4>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="oracle-kpi-card">
          <div class="oracle-kpi-icon">
            <i class="bi bi-exclamation-triangle"></i>
          </div>
          <div>
            <div class="text-muted small">High Risk Changes</div>
            <h4 class="mb-0">{{ stats.high_risk_changes }}</h4>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="oracle-kpi-card">
          <div class="oracle-kpi-icon">
            <i class="bi bi-check-circle"></i>
          </div>
          <div>
            <div class="text-muted small">Total Changes</div>
            <h4 class="mb-0">{{ stats.total_changes }}</h4>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Chart -->
      <div class="col-md-8">
        <div class="card oracle-chart-card">
          <div class="card-header">
            <h5 class="mb-0">Change Requests per Month ({{ currentYear }})</h5>
          </div>
          <div class="card-body">
            <canvas ref="chartCanvas" height="100"></canvas>
          </div>
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="col-md-4">
        <div class="card oracle-chart-card">
          <div class="card-header">
            <h5 class="mb-0">Recent Activities</h5>
          </div>
          <div class="card-body p-0">
            <div class="activity-list">
              <div
                v-for="activity in recentActivities"
                :key="activity.id"
                class="activity-item"
              >
                <div class="activity-icon">
                  <i class="bi bi-circle-fill"></i>
                </div>
                <div class="activity-content">
                  <p class="mb-1">
                    <strong>{{ activity.user }}</strong> {{ activity.description }}
                  </p>
                  <small class="text-muted">{{ formatDate(activity.created_at) }}</small>
                </div>
              </div>
              <div v-if="recentActivities.length === 0" class="p-3 text-center text-muted">
                No recent activities
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Chart, registerables } from 'chart.js'
import api from '@/services/api'

Chart.register(...registerables)

const stats = ref({
  open_requests: 0,
  pending_approvals: 0,
  high_risk_changes: 0,
  total_changes: 0
})

const recentActivities = ref([])
const chartCanvas = ref(null)
const currentYear = new Date().getFullYear()
let chartInstance = null

const loadDashboardData = async () => {
  try {
    const response = await api.get('/dashboard')
    if (response.data.success) {
      stats.value = response.data.data.statistics
      recentActivities.value = response.data.data.recent_activities

      // Create chart
      if (chartCanvas.value) {
        createChart(response.data.data.charts.changes_per_month)
      }
    }
  } catch (error) {
    console.error('Failed to load dashboard data:', error)
  }
}

const createChart = (data) => {
  const ctx = chartCanvas.value.getContext('2d')

  if (chartInstance) {
    chartInstance.destroy()
  }

  chartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      datasets: [
        {
          label: 'Change Requests',
          data: data,
          borderColor: '#e60000',
          backgroundColor: 'rgba(230, 0, 0, 0.1)',
          tension: 0.4,
          fill: true
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      }
    }
  })
}

const formatDate = (dateString) => {
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

onMounted(() => {
  loadDashboardData()
})
</script>

<style scoped>
.oracle-summary {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  background: linear-gradient(120deg, #252a32, #303744);
  border-radius: 14px;
  padding: 1rem 1.1rem;
  color: #f3f6fb;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.oracle-summary-kicker {
  color: #ffb7ac;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  font-size: 0.72rem;
}

.oracle-summary-badge {
  color: #8a1f11 !important;
}

.oracle-kpi-card {
  border-radius: 12px;
  border: 1px solid var(--border-color);
  border-top: 3px solid var(--topbar-color);
  background: #fff;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
  padding: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.8rem;
  transition: transform 0.2s ease;
}

.oracle-kpi-card:hover {
  transform: translateY(-4px);
}

.oracle-kpi-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: #fff2ef;
  color: var(--topbar-color);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

.oracle-chart-card {
  border-top: 3px solid var(--topbar-color) !important;
}

.activity-list {
  max-height: 400px;
  overflow-y: auto;
}

.activity-item {
  display: flex;
  padding: 15px 20px;
  border-bottom: 1px solid #f0f0f0;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  margin-right: 15px;
  color: #e60000;
  font-size: 10px;
  padding-top: 5px;
}

.activity-content {
  flex: 1;
}

.activity-content p {
  font-size: 14px;
  line-height: 1.5;
}
</style>
