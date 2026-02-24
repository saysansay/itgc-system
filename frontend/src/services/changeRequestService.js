import api from './api'

const changeRequestService = {
  async getAll(params = {}) {
    const response = await api.get('/change-requests', { params })
    return response.data
  },

  async getById(id) {
    const response = await api.get(`/change-requests/${id}`)
    return response.data
  },

  async create(data) {
    const response = await api.post('/change-requests', data)
    return response.data
  },

  async update(id, data) {
    const response = await api.put(`/change-requests/${id}`, data)
    return response.data
  },

  async delete(id) {
    const response = await api.delete(`/change-requests/${id}`)
    return response.data
  },

  async submitForApproval(id) {
    const response = await api.post(`/change-requests/${id}/submit`)
    return response.data
  },

  async uploadEvidence(id, file, description) {
    const formData = new FormData()
    formData.append('file', file)
    if (description) {
      formData.append('description', description)
    }

    const response = await api.post(`/change-requests/${id}/evidence`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return response.data
  },
  async exportChangeRequests(params = {}) {
    return api.get('/change-requests/export', {
      params,
      responseType: 'blob'
    })
  }
}

export default changeRequestService
