import api from './api'

export default {
  getAccessRequests(params = {}) {
    return api.get('/access-requests', { params })
  },

  getAccessRequest(id) {
    return api.get(`/access-requests/${id}`)
  },

  createAccessRequest(data) {
    return api.post('/access-requests', data)
  },

  updateAccessRequest(id, data) {
    return api.put(`/access-requests/${id}`, data)
  },

  deleteAccessRequest(id) {
    return api.delete(`/access-requests/${id}`)
  },

  getSystems() {
    return api.get('/systems', { params: { per_page: 100, is_active: 1 } })
  },
  exportAccessRequests(params = {}) {
    return api.get('/access-requests/export', {
      params,
      responseType: 'blob'
    })
  }
}
