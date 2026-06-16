import { defineStore } from 'pinia'
import api from '@/api'

export const useDataStore = defineStore('data', {
  state: () => ({
    notifikasi: [], unreadCount: 0,
    inventaris: [], peminjaman: [],
    kerusakan: [], insiden: [], modul: [],
    matkul: [], jadwal: [], ruangan: [],
    absensi: [], nilai: [], pemeliharaan: [],
    users: [],
  }),
  actions: {
    // Notifikasi — titik merah
    async fetchNotifikasi() {
      const { data } = await api.get('/notifikasi')
      this.notifikasi = data
      this.unreadCount = data.filter(n => !n.dibaca).length
    },
    async fetchUnreadCount() {
      const { data } = await api.get('/notifikasi/unread-count')
      this.unreadCount = data.unread
    },
    async tandaiSemuaBaca() {
      await api.post('/notifikasi/baca-semua')
      this.unreadCount = 0
      this.notifikasi = this.notifikasi.map(n => ({ ...n, dibaca: true }))
    },

    async fetchInventaris()   { this.inventaris   = (await api.get('/inventaris')).data },
    async fetchPeminjaman()   { this.peminjaman   = (await api.get('/peminjaman')).data },
    async fetchKerusakan()    { this.kerusakan    = (await api.get('/kerusakan')).data },
    async fetchInsiden()      { this.insiden      = (await api.get('/insiden')).data },
    async fetchModul()        { this.modul        = (await api.get('/modul')).data },
    async fetchMatkul()       { this.matkul       = (await api.get('/matkul')).data },
    async fetchJadwal()       { this.jadwal       = (await api.get('/jadwal')).data },
    async fetchRuangan()      { this.ruangan      = (await api.get('/ruangan')).data },
    async fetchAbsensi()      { this.absensi      = (await api.get('/absensi')).data },
    async fetchNilai()        { this.nilai        = (await api.get('/nilai')).data },
    async fetchPemeliharaan() { this.pemeliharaan = (await api.get('/pemeliharaan')).data },
    async fetchUsers()        { this.users        = (await api.get('/users')).data },
  },
})
