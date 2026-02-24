import api from './api'

export default {
  getItAssets(params = {}) {
    return api.get('/it-assets', { params })
  },

  getItAsset(id) {
    return api.get(`/it-assets/${id}`)
  },

  createItAsset(data) {
    return api.post('/it-assets', data)
  },

  updateItAsset(id, data) {
    return api.put(`/it-assets/${id}`, data)
  },

  deleteItAsset(id) {
    return api.delete(`/it-assets/${id}`)
  },

  assignAsset(id, data) {
    return api.post(`/it-assets/${id}/assign`, data)
  },

  returnAsset(id, data) {
    return api.post(`/it-assets/${id}/return`, data)
  },

  getDepartments() {
    return api.get('/departments/list')
  },

  getUsers() {
    return api.get('/users', { params: { per_page: 100 } })
  },
  exportItAssets(params = {}) {
    return api.get('/it-assets/export', {
      params,
      responseType: 'blob'
    })
  }
}
