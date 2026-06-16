import { defineStore } from 'pinia'

export const useUiStore = defineStore('ui', {
  state: () => ({
    modal: null, // { title, message, type }
  }),
  actions: {
    showSuccess(title, message) { this.modal = { title, message, type: 'success' } },
    showError(title, message)   { this.modal = { title, message, type: 'error' } },
    closeModal() { this.modal = null },
  },
})
