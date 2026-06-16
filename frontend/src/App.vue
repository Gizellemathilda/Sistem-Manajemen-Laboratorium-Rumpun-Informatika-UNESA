<template>
  <router-view v-if="!auth.isAuth" />

  <div v-else class="app-shell">
    <aside class="sidebar">
      <h1><FlaskConical :size="22" /> SimLab</h1>
      <div class="user-mini">
        <img :src="auth.user?.foto || avatarFallback" alt="profil"/>
        <div>
          <div class="nm">{{ auth.user?.nama }}</div>
          <div class="rl">{{ roleLabel }}</div>
        </div>
      </div>
      <nav>
        <router-link v-for="m in menus" :key="m.to" :to="m.to">
          <span><component :is="m.icon" :size="16" /> {{ m.label }}</span>
          <span v-if="m.to==='/peminjaman' && pendingPeminjaman>0 && auth.role==='dosen'"
                class="nav-badge">{{ pendingPeminjaman }}</span>
        </router-link>
        <a href="#" @click.prevent="logout" style="margin-top:24px;color:#fca5a5">
          <LogOut :size="16" /> Keluar
        </a>
      </nav>
    </aside>

    <main class="main">
      <header class="topbar">
        <h2>{{ pageTitle }}</h2>
        <button class="notif-btn" @click="toggleNotifPanel" id="notif-btn">
          <Bell :size="20" />
          <span v-if="data.unreadCount > 0" class="notif-dot" id="notif-dot"></span>
        </button>
      </header>

      <div v-if="notifOpen" class="notif-panel">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
          <strong>Notifikasi</strong>
          <button class="btn secondary" style="padding:4px 8px;font-size:12px" @click="closeAndMarkRead">
            Tandai semua dibaca
          </button>
        </div>
        <div v-if="data.notifikasi.length === 0" style="text-align:center;padding:20px;color:#94a3b8">
          Tidak ada notifikasi
        </div>
        <div v-for="n in data.notifikasi.slice(0,8)" :key="n.id"
             class="notif-item" :class="{ unread: !n.dibaca }">
          <div class="t">{{ n.judul }}</div>
          <div class="m">{{ n.pesan }}</div>
        </div>
      </div>

      <section class="content">
        <router-view />
      </section>
    </main>

    <!-- Pop-up global -->
    <div v-if="ui.modal" class="modal-mask" @click.self="ui.closeModal()">
      <div class="modal">
        <h3>{{ ui.modal.title }}</h3>
        <p>{{ ui.modal.message }}</p>
        <div class="actions">
          <button class="btn" @click="ui.closeModal()">OK</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useDataStore } from '@/stores/data'
import { useUiStore } from '@/stores/ui'
import {
  FlaskConical,
  Home,
  User,
  CalendarDays,
  Package,
  ClipboardList,
  BookOpen,
  TriangleAlert,
  Siren,
  CheckSquare,
  BarChart2,
  Bell,
  BookMarked,
  DoorOpen,
  Wrench,
  Users,
  LogOut,
} from 'lucide-vue-next'

const auth = useAuthStore(); const data = useDataStore(); const ui = useUiStore()
const route = useRoute(); const router = useRouter()
const notifOpen = ref(false)

const avatarFallback = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80"><circle cx="40" cy="40" r="40" fill="%2394a3b8"/><text x="50%25" y="55%25" text-anchor="middle" font-size="32" fill="white">U</text></svg>'

const ROLE_LABEL = {
  mahasiswa: 'Mahasiswa', asprak: 'Asisten Praktikum',
  aslab: 'Asisten Lab', dosen: 'Dosen', admin: 'Administrator',
}
const roleLabel = computed(() => ROLE_LABEL[auth.role] || '')

// Menu per-role mengikuti aturan simlab-4.html
const MENU_BY_ROLE = {
  mahasiswa: [
    ['/', Home, 'Dashboard'], ['/profil', User, 'Profil'],
    ['/jadwal', CalendarDays, 'Jadwal'], ['/inventaris', Package, 'Inventaris'],
    ['/peminjaman', ClipboardList, 'Peminjaman'], ['/modul', BookOpen, 'Modul'],
    ['/lapor-kerusakan', TriangleAlert, 'Lapor Kerusakan'],
    ['/lapor-insiden', Siren, 'Lapor Insiden'],
    ['/absensi', CheckSquare, 'Absensi'], ['/nilai', BarChart2, 'Nilai'],
    ['/notifikasi', Bell, 'Notifikasi'],
  ],
  asprak: [
    ['/', Home, 'Dashboard'], ['/profil', User, 'Profil'],
    ['/jadwal', CalendarDays, 'Jadwal'], ['/inventaris', Package, 'Inventaris'],
    ['/peminjaman', ClipboardList, 'Peminjaman'], ['/modul', BookOpen, 'Modul'],
    ['/lapor-kerusakan', TriangleAlert, 'Lapor Kerusakan'],
    ['/lapor-insiden', Siren, 'Lapor Insiden'],
    ['/absensi', CheckSquare, 'Absensi'], ['/nilai', BarChart2, 'Nilai'],
    ['/notifikasi', Bell, 'Notifikasi'],
  ],
  // Aslab: tanpa absensi, nilai, modul
  aslab: [
    ['/', Home, 'Dashboard'], ['/profil', User, 'Profil'],
    ['/inventaris', Package, 'Inventaris'], ['/ruangan', DoorOpen, 'Ruangan'],
    ['/peminjaman', ClipboardList, 'Peminjaman'], ['/kerusakan', TriangleAlert, 'Kerusakan'],
    ['/insiden', Siren, 'Insiden'], ['/pemeliharaan', Wrench, 'Pemeliharaan'],
    ['/jadwal', CalendarDays, 'Jadwal'], ['/notifikasi', Bell, 'Notifikasi'],
  ],
  // Dosen: tanpa menu jadwal, absensi read-only
  dosen: [
    ['/', Home, 'Dashboard'], ['/profil', User, 'Profil'],
    ['/matkul', BookMarked, 'Mata Kuliah'], ['/inventaris', Package, 'Inventaris'],
    ['/ruangan', DoorOpen, 'Ruangan'],
    ['/peminjaman', ClipboardList, 'Validasi Peminjaman'],
    ['/kerusakan', TriangleAlert, 'Riwayat Kerusakan'],
    ['/absensi', CheckSquare, 'Riwayat Absensi'], ['/nilai', BarChart2, 'Nilai'],
    ['/modul', BookOpen, 'Modul'], ['/notifikasi', Bell, 'Notifikasi'],
  ],
  admin: [
    ['/', Home, 'Dashboard'], ['/profil', User, 'Profil'],
    ['/users', Users, 'Pengguna'], ['/ruangan', DoorOpen, 'Ruangan'],
    ['/matkul', BookMarked, 'Mata Kuliah'], ['/jadwal', CalendarDays, 'Jadwal'],
    ['/inventaris', Package, 'Inventaris'], ['/peminjaman', ClipboardList, 'Peminjaman'],
    ['/kerusakan', TriangleAlert, 'Kerusakan'], ['/insiden', Siren, 'Insiden'],
    ['/pemeliharaan', Wrench, 'Pemeliharaan'],
    ['/notifikasi', Bell, 'Notifikasi'],
  ],
}
const menus = computed(() =>
  (MENU_BY_ROLE[auth.role] || []).map(([to, icon, label]) => ({ to, icon, label }))
)

const titles = {
  '/': 'Dashboard', '/profil': 'Profil Saya', '/inventaris': 'Inventaris',
  '/peminjaman': 'Peminjaman', '/lapor-kerusakan': 'Lapor Kerusakan',
  '/lapor-insiden': 'Lapor Insiden', '/kerusakan': 'Daftar Kerusakan',
  '/insiden': 'Daftar Insiden', '/modul': 'Modul Praktikum',
  '/notifikasi': 'Notifikasi', '/matkul': 'Mata Kuliah',
  '/jadwal': 'Jadwal Praktikum', '/ruangan': 'Ruangan',
  '/absensi': 'Absensi', '/nilai': 'Nilai',
  '/pemeliharaan': 'Pemeliharaan', '/users': 'Manajemen Pengguna',
}
const pageTitle = computed(() => titles[route.path] || 'SimLab')

const pendingPeminjaman = computed(
  () => data.peminjaman.filter(p => p.status === 'menunggu').length
)

onMounted(async () => {
  if (auth.isAuth) {
    await auth.fetchMe?.()
    await data.fetchNotifikasi().catch(()=>{})
    if (auth.role === 'dosen') data.fetchPeminjaman().catch(()=>{})
  }
})

watch(() => route.path, () => { notifOpen.value = false })

async function toggleNotifPanel() {
  notifOpen.value = !notifOpen.value
  if (notifOpen.value && data.unreadCount > 0) {
    await data.tandaiSemuaBaca()
  }
}
async function closeAndMarkRead() {
  await data.tandaiSemuaBaca()
  notifOpen.value = false
}
async function logout() {
  await auth.logout(); router.push('/login')
}
</script>
