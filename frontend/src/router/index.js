import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

import LoginView from '@/views/LoginView.vue'
import RegisterView from '@/views/RegisterView.vue'
import DashboardView from '@/views/DashboardView.vue'
import ProfilView from '@/views/ProfilView.vue'
import InventarisView from '@/views/InventarisView.vue'
import PeminjamanView from '@/views/PeminjamanView.vue'
import LaporKerusakanView from '@/views/LaporKerusakanView.vue'
import LaporInsidenView from '@/views/LaporInsidenView.vue'
import KerusakanListView from '@/views/KerusakanListView.vue'
import InsidenListView from '@/views/InsidenListView.vue'
import ModulView from '@/views/ModulView.vue'
import NotifikasiView from '@/views/NotifikasiView.vue'
import MatkulView from '@/views/MatkulView.vue'
import JadwalView from '@/views/JadwalView.vue'
import RuanganView from '@/views/RuanganView.vue'
import AbsensiView from '@/views/AbsensiView.vue'
import NilaiView from '@/views/NilaiView.vue'
import PemeliharaanView from '@/views/PemeliharaanView.vue'
import UserManagementView from '@/views/UserManagementView.vue'
import LaporanView from '@/views/LaporanView.vue'
import BookingRuangView from '@/views/BookingRuangView.vue'

const routes = [
  { path: '/login', component: LoginView, meta: { guest: true } },
  { path: '/register', component: RegisterView, meta: { guest: true } },
  { path: '/', component: DashboardView },
  { path: '/profil', component: ProfilView },
  { path: '/inventaris', component: InventarisView },
  { path: '/peminjaman', component: PeminjamanView },
  { path: '/lapor-kerusakan', component: LaporKerusakanView },
  { path: '/lapor-insiden', component: LaporInsidenView },
  { path: '/kerusakan', component: KerusakanListView },
  { path: '/insiden', component: InsidenListView },
  { path: '/modul', component: ModulView },
  { path: '/notifikasi', component: NotifikasiView },
  { path: '/matkul', component: MatkulView },
  { path: '/jadwal', component: JadwalView },
  { path: '/ruangan', component: RuanganView },
  { path: '/absensi', component: AbsensiView },
  { path: '/nilai', component: NilaiView },
  { path: '/pemeliharaan', component: PemeliharaanView },
  { path: '/users', component: UserManagementView, meta: { roles: ['admin'] } },
  { path: '/laporan', component: LaporanView },
  { path: '/booking-ruang', component: BookingRuangView },
]

const router = createRouter({ history: createWebHistory(), routes })

router.beforeEach((to) => {
  const auth = useAuthStore()
  if (!to.meta.guest && !auth.isAuth) return '/login'
  if (to.meta.guest && auth.isAuth) return '/'
  if (to.meta.roles && !to.meta.roles.includes(auth.role)) return '/'
})

export default router