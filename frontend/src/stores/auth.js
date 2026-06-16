import { defineStore } from 'pinia'
import api from '@/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('simlab_user') || 'null'),
    token: localStorage.getItem('simlab_token') || null,
  }),
  getters: {
    isAuth: (s) => !!s.token,
    role: (s) => s.user?.role || null,
  },
  actions: {
    async login(email, password, role) {
      const { data } = await api.post('/login', { email, password, role })
      this.user = data.user
      this.token = data.token
      localStorage.setItem('simlab_token', data.token)
      localStorage.setItem('simlab_user', JSON.stringify(data.user))
    },
    async googleLogin(email, name = null) {
      const { data } = await api.post('/auth/google', { email, name })
      this.user = data.user
      this.token = data.token
      localStorage.setItem('simlab_token', data.token)
      localStorage.setItem('simlab_user', JSON.stringify(data.user))
    },
    async fetchMe() {
      const { data } = await api.get('/me')
      this.user = data
      localStorage.setItem('simlab_user', JSON.stringify(data))
    },
    updateUserLocal(patch) {
      this.user = { ...this.user, ...patch }
      localStorage.setItem('simlab_user', JSON.stringify(this.user))
    },
    async logout() {
      try { await api.post('/logout') } catch {}
      this.user = null; this.token = null
      localStorage.removeItem('simlab_token'); localStorage.removeItem('simlab_user')
    },
  },
})
