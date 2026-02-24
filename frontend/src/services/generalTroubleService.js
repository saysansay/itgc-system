import api from './api'

export default {
  getGeneralTroubles(params = {}) {
    return api.get('/general-troubles', { params })
  },

  getGeneralTrouble(id) {
    return api.get(`/general-troubles/${id}`)
  },

  createGeneralTrouble(data) {
    return api.post('/general-troubles', data)
  },

  updateGeneralTrouble(id, data) {
    return api.put(`/general-troubles/${id}`, data)
  },

  deleteGeneralTrouble(id) {
    return api.delete(`/general-troubles/${id}`)
  },

  getUsers() {
    return api.get('/users', { params: { per_page: 100 } })
  },

  getItAdminUsers() {
    return api.get('/usb-loans/it-admin-users')
  },
  exportGeneralTroubles(params = {}) {
    return api.get('/general-troubles/export', {
      params,
      responseType: 'blob'
    })
  }
}
