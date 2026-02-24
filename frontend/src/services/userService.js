import api from './api'

export default {
  getUsers(params = {}) {
    return api.get('/users', { params })
  },

  getUser(id) {
    return api.get(`/users/${id}`)
  },

  createUser(data) {
    return api.post('/users', data)
  },

  updateUser(id, data) {
    return api.put(`/users/${id}`, data)
  },

  deleteUser(id) {
    return api.delete(`/users/${id}`)
  },

  getRoles() {
    return api.get('/roles/list')
  },

  getDepartments() {
    return api.get('/departments/list')
  }
}
