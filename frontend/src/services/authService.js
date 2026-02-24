import api from './api'

const authService = {
  async login(credentials) {
    try {
      const response = await api.post('/login', credentials)
      if (response.data.success) {
        const { token, user } = response.data.data
        localStorage.setItem('token', token)
        localStorage.setItem('user', JSON.stringify(user))
        return response.data
      }
      throw new Error(response.data.message)
    } catch (error) {
      throw error.response?.data || error
    }
  },

  async register(userData) {
    try {
      const response = await api.post('/register', userData)
      if (response.data.success) {
        const { token, user } = response.data.data
        localStorage.setItem('token', token)
        localStorage.setItem('user', JSON.stringify(user))
        return response.data
      }
      throw new Error(response.data.message)
    } catch (error) {
      throw error.response?.data || error
    }
  },

  async logout() {
    try {
      await api.post('/logout')
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    }
  },

  async changePassword(payload) {
    try {
      const response = await api.post('/change-password', payload)
      if (response.data.success) {
        return response.data
      }
      throw new Error(response.data.message)
    } catch (error) {
      throw error.response?.data || error
    }
  },

  async forgotPassword(payload) {
    try {
      const response = await api.post('/forgot-password', payload)
      if (response.data.success) {
        return response.data
      }
      throw new Error(response.data.message)
    } catch (error) {
      throw error.response?.data || error
    }
  },

  async resetPassword(payload) {
    try {
      const response = await api.post('/reset-password', payload)
      if (response.data.success) {
        return response.data
      }
      throw new Error(response.data.message)
    } catch (error) {
      throw error.response?.data || error
    }
  },

  async me() {
    try {
      const response = await api.get('/me')
      if (response.data.success) {
        localStorage.setItem('user', JSON.stringify(response.data.data))
        return response.data.data
      }
    } catch (error) {
      throw error.response?.data || error
    }
  },

  isAuthenticated() {
    return !!localStorage.getItem('token')
  },

  getUser() {
    const user = localStorage.getItem('user')
    return user ? JSON.parse(user) : null
  },

  hasRole(role) {
    const user = this.getUser()
    return user?.roles?.some(r => r.slug === role) || false
  },

  hasPermission(permission) {
    const user = this.getUser()
    if (!user?.roles) return false
    
    return user.roles.some(role => 
      role.permissions?.some(p => p.slug === permission)
    ) || false
  }
}

export default authService
