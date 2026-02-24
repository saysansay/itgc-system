import api from './api'

export default {
  getSystems(params = {}) {
    return api.get('/systems', { params })
  },

  getSystem(id) {
    return api.get(`/systems/${id}`)
  },

  createSystem(data) {
    return api.post('/systems', data)
  },

  updateSystem(id, data) {
    return api.put(`/systems/${id}`, data)
  },

  deleteSystem(id) {
    return api.delete(`/systems/${id}`)
  }
}
