import api from './api'

export default {
  getAdminAccessRequests(params = {}) {
    return api.get('/admin-access-requests', { params })
  },

  getAdminAccessRequest(id) {
    return api.get(`/admin-access-requests/${id}`)
  },

  createAdminAccessRequest(data) {
    return api.post('/admin-access-requests', data)
  },

  updateAdminAccessRequest(id, data) {
    return api.put(`/admin-access-requests/${id}`, data)
  },

  deleteAdminAccessRequest(id) {
    return api.delete(`/admin-access-requests/${id}`)
  },

  approveAdminAccessRequest(id, data) {
    return api.post(`/admin-access-requests/${id}/approve`, data)
  },

  rejectAdminAccessRequest(id, data) {
    return api.post(`/admin-access-requests/${id}/reject`, data)
  },

  getUsers() {
    return api.get('/users', { params: { per_page: 100 } })
  },

  getDepartments() {
    return api.get('/departments/list')
  },
  exportAdminAccessRequests(params = {}) {
    return api.get('/admin-access-requests/export', {
      params,
      responseType: 'blob'
    })
  }
}
