import api from './api'

export default {
  getSecurityAccessRequests(params = {}) {
    return api.get('/security-access-requests', { params })
  },

  getSecurityAccessRequest(id) {
    return api.get(`/security-access-requests/${id}`)
  },

  createSecurityAccessRequest(data) {
    return api.post('/security-access-requests', data)
  },

  updateSecurityAccessRequest(id, data) {
    return api.post(`/security-access-requests/${id}?_method=PUT`, data)
  },

  deleteSecurityAccessRequest(id) {
    return api.delete(`/security-access-requests/${id}`)
  },

  approveSecurityAccessRequest(id) {
    return api.post(`/security-access-requests/${id}/approve`)
  },

  rejectSecurityAccessRequest(id) {
    return api.post(`/security-access-requests/${id}/reject`)
  },

  completeSecurityAccessRequest(id) {
    return api.post(`/security-access-requests/${id}/complete`)
  },

  deleteSecurityAccessAttachment(requestId, attachmentId) {
    return api.delete(`/security-access-requests/${requestId}/attachments/${attachmentId}`)
  },

  getUsers() {
    return api.get('/users', { params: { per_page: 100 } })
  },

  getDepartments() {
    return api.get('/departments/list')
  },
  exportSecurityAccessRequests(params = {}) {
    return api.get('/security-access-requests/export', {
      params,
      responseType: 'blob'
    })
  }
}
