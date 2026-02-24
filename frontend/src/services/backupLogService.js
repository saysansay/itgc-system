import api from './api'

export default {
  getBackupLogs(params = {}) {
    return api.get('/backup-logs', { params })
  },

  getBackupLog(id) {
    return api.get(`/backup-logs/${id}`)
  },

  createBackupLog(data) {
    return api.post('/backup-logs', data)
  },

  updateBackupLog(id, data) {
    return api.put(`/backup-logs/${id}`, data)
  },

  deleteBackupLog(id) {
    return api.delete(`/backup-logs/${id}`)
  },

  verifyBackupLog(id, data) {
    return api.post(`/backup-logs/${id}/verify`, data)
  },

  getSystems() {
    return api.get('/systems', { params: { per_page: 100 } })
  },
  exportBackupLogs(params = {}) {
    return api.get('/backup-logs/export', {
      params,
      responseType: 'blob'
    })
  }
}
