import api from './api'

export default {
  getUsbLoans(params = {}) {
    return api.get('/usb-loans', { params })
  },

  getUsbLoan(id) {
    return api.get(`/usb-loans/${id}`)
  },

  createUsbLoan(data) {
    return api.post('/usb-loans', data)
  },

  updateUsbLoan(id, data) {
    return api.put(`/usb-loans/${id}`, data)
  },

  deleteUsbLoan(id) {
    return api.delete(`/usb-loans/${id}`)
  },

  approveUsbLoan(id, data) {
    return api.post(`/usb-loans/${id}/approve`, data)
  },

  rejectUsbLoan(id, data) {
    return api.post(`/usb-loans/${id}/reject`, data)
  },

  returnUsbLoan(id, data) {
    return api.post(`/usb-loans/${id}/return`, data)
  },

  getUsers() {
    return api.get('/users', { params: { per_page: 100 } })
  },

  getDepartments() {
    return api.get('/departments/list')
  },

  getItAdminUsers() {
    return api.get('/usb-loans/it-admin-users')
  },
  exportUsbLoans(params = {}) {
    return api.get('/usb-loans/export', {
      params,
      responseType: 'blob'
    })
  }
}
