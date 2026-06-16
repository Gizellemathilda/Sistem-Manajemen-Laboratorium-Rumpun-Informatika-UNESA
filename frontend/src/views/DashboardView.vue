<template>
  <div>
    <!-- ── MAHASISWA ── -->
    <template v-if="role === 'mahasiswa'">
      <div class="page-heading">
        <GraduationCap class="heading-icon" />
        <div>
          <h1>Selamat datang, {{ firstName }}! <Smile class="inline-icon" /></h1>
          <p>Ringkasan aktivitas Lab Anda hari ini.</p>
        </div>
      </div>
      <div class="grid grid-4 mb-6">
        <StatCard :icon="Briefcase" color="blue"  :value="myPinjam.length"          label="Total Peminjaman" />
        <StatCard :icon="Clock"     color="amber" :value="myPinjamPending.length"   label="Menunggu Persetujuan" />
        <StatCard :icon="BookOpen"  color="green" :value="data.modul.length"        label="Modul Tersedia" />
        <StatCard :icon="Bell"      color="red"   :value="data.unreadCount"         label="Notifikasi Baru" />
      </div>
      <div class="grid grid-2">
        <div class="card">
          <div class="card-header">
            <div class="card-title"><CalendarDays class="icon-sm" /> Jadwal Praktikum Mendatang</div>
            <router-link to="/jadwal" class="btn btn-outline btn-sm">Lihat Semua</router-link>
          </div>
          <table><thead><tr>
            <th><BookOpen class="icon-xs" /> Mata Kuliah</th>
            <th><CalendarDays class="icon-xs" /> Hari</th>
            <th><Clock class="icon-xs" /> Jam</th>
          </tr></thead>
            <tbody>
              <tr v-for="j in data.jadwal.slice(0,3)" :key="j.id">
                <td><b>{{ j.matkul }}</b></td><td>{{ j.hari }}</td><td>{{ j.jam }}</td>
              </tr>
              <tr v-if="!data.jadwal.length">
                <td colspan="3" class="empty-cell"><CalendarOff class="empty-icon-inline" /> Belum ada jadwal.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="card-title"><PackageSearch class="icon-sm" /> Status Peminjaman Saya</div>
            <router-link to="/peminjaman" class="btn btn-success btn-sm">
              <Plus class="icon-xs" /> Ajukan
            </router-link>
          </div>
          <table v-if="myPinjam.length"><thead><tr>
            <th><Package class="icon-xs" /> Aset</th>
            <th><CalendarDays class="icon-xs" /> Tgl</th>
            <th><Activity class="icon-xs" /> Status</th>
          </tr></thead>
            <tbody>
              <tr v-for="p in myPinjam.slice(0,4)" :key="p.id">
                <td>{{ p.inventaris?.nama ?? '-' }}</td>
                <td>{{ p.tgl_pinjam }}</td>
                <td><span :class="badgeClass(p.status)">{{ p.status }}</span></td>
              </tr>
            </tbody>
          </table>
          <div v-else class="empty-state">
            <MailOpen class="empty-icon" /><p>Belum ada peminjaman.</p>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="card-title"><FileText class="icon-sm" /> Modul Terbaru</div>
            <router-link to="/modul" class="btn btn-outline btn-sm">Lihat Semua</router-link>
          </div>
          <div v-for="m in data.modul.slice(0,3)" :key="m.id" class="modul-item">
            <div class="modul-icon"><FileText class="modul-icon-svg" /></div>
            <div class="modul-info">
              <div class="modul-title">{{ m.judul }}</div>
              <div class="modul-sub">{{ m.mata_kuliah }}</div>
            </div>
          </div>
          <div v-if="!data.modul.length" class="empty-state">
            <FolderOpen class="empty-icon" /><p>Belum ada modul.</p>
          </div>
        </div>
      </div>
    </template>

    <!-- ── ASPRAK ── -->
    <template v-else-if="role === 'asprak'">
      <div class="page-heading">
        <UserCheck class="heading-icon" />
        <div>
          <h1>Selamat datang, {{ firstName }}! <Smile class="inline-icon" /></h1>
          <p>Panel Asisten Praktikum.</p>
        </div>
      </div>
      <div class="grid grid-4 mb-6">
        <StatCard :icon="Calendar"  color="blue"  :value="data.jadwal.length"  label="Jadwal Praktikum" />
        <StatCard :icon="BarChart3" color="green" :value="data.nilai.length"   label="Data Nilai" />
        <StatCard :icon="CheckCheck" color="amber" :value="data.absensi.length" label="Sesi Absensi" />
        <StatCard :icon="BookOpen"  color="red"   :value="data.modul.length"   label="Modul Tersedia" />
      </div>
      <div class="grid grid-2">
        <div class="card">
          <div class="card-header"><div class="card-title"><Zap class="icon-sm" /> Quick Actions</div></div>
          <div class="quick-actions">
            <router-link to="/nilai"    class="btn btn-primary"><BarChart3 class="icon-btn" /> Input Nilai Mahasiswa</router-link>
            <router-link to="/modul"   class="btn btn-success"><Upload class="icon-btn" /> Unggah Modul Baru</router-link>
            <router-link to="/absensi" class="btn btn-warning"><ClipboardCheck class="icon-btn" /> Input Absensi</router-link>
          </div>
        </div>
        <div class="card">
          <div class="card-header"><div class="card-title"><ClipboardList class="icon-sm" /> Absensi Terkini</div></div>
          <table><thead><tr>
            <th><CalendarDays class="icon-xs" /> Tanggal</th>
            <th><BookOpen class="icon-xs" /> Matkul</th>
            <th><Users class="icon-xs" /> Hadir</th>
          </tr></thead>
            <tbody>
              <tr v-for="a in data.absensi.slice(0,4)" :key="a.id">
                <td>{{ a.tanggal }}</td><td>{{ a.matkul }}</td>
                <td><span class="badge badge-green">{{ a.hadir }}/{{ a.total ?? '-' }}</span></td>
              </tr>
              <tr v-if="!data.absensi.length">
                <td colspan="3" class="empty-cell"><ClipboardX class="empty-icon-inline" /> Belum ada data absensi.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <!-- ── ASLAB ── -->
    <template v-else-if="role === 'aslab'">
      <div class="page-heading">
        <FlaskConical class="heading-icon" />
        <div>
          <h1>Dashboard Asisten Lab</h1>
          <p>Pantau inventaris dan kondisi laboratorium.</p>
        </div>
      </div>
      <div class="grid grid-4 mb-6">
        <StatCard :icon="Briefcase"    color="blue"  :value="data.inventaris.length"                                       label="Total Aset" />
        <StatCard :icon="AlertTriangle" color="red"   :value="data.inventaris.filter(i=>i.kondisi==='Rusak').length"        label="Aset Rusak" />
        <StatCard :icon="Wrench"       color="amber" :value="data.kerusakan.length"                                        label="Laporan Kerusakan" />
        <StatCard :icon="Zap"          color="green" :value="data.pemeliharaan.filter(p=>p.status==='Dijadwalkan').length"  label="Pemeliharaan Aktif" />
      </div>
      <div class="grid grid-2">
        <div class="card">
          <div class="card-header">
            <div class="card-title"><PackageSearch class="icon-sm" /> Status Inventaris</div>
            <router-link to="/inventaris" class="btn btn-outline btn-sm">Kelola</router-link>
          </div>
          <table><thead><tr>
            <th><Package class="icon-xs" /> Nama</th>
            <th><ShieldCheck class="icon-xs" /> Kondisi</th>
            <th><Activity class="icon-xs" /> Status</th>
          </tr></thead>
            <tbody>
              <tr v-for="i in data.inventaris.slice(0,5)" :key="i.id">
                <td>{{ i.nama }}</td>
                <td><span :class="badgeKondisi(i.kondisi)">{{ i.kondisi }}</span></td>
                <td><span :class="badgeClass(i.status)">{{ i.status }}</span></td>
              </tr>
              <tr v-if="!data.inventaris.length">
                <td colspan="3" class="empty-cell"><PackageX class="empty-icon-inline" /> Belum ada aset.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="card">
          <div class="card-header">
            <div class="card-title"><CalendarCog class="icon-sm" /> Pemeliharaan Mendatang</div>
            <router-link to="/pemeliharaan" class="btn btn-outline btn-sm">
              <Plus class="icon-xs" /> Tambah
            </router-link>
          </div>
          <div v-for="p in data.pemeliharaan.filter(x=>x.status==='Dijadwalkan').slice(0,4)" :key="p.id" class="list-item">
            <div class="list-item-inner">
              <Wrench class="list-icon" />
              <div>
                <div class="list-title">{{ p.alat }}</div>
                <div class="list-sub"><CalendarDays class="icon-xs" /> {{ p.tanggal }}</div>
              </div>
            </div>
          </div>
          <div v-if="!data.pemeliharaan.filter(x=>x.status==='Dijadwalkan').length" class="empty-state">
            <CalendarCheck class="empty-icon" /><p>Tidak ada jadwal mendatang.</p>
          </div>
        </div>
      </div>
    </template>

    <!-- ── DOSEN ── -->
    <template v-else-if="role === 'dosen'">
      <div class="page-heading">
        <GraduationCap class="heading-icon" />
        <div>
          <h1>Selamat datang, {{ firstName }}! <Smile class="inline-icon" /></h1>
          <p>Panel Dosen.</p>
        </div>
      </div>
      <div class="grid grid-4 mb-6">
        <StatCard :icon="ClipboardCheck" color="amber" :value="peminjamanPending.length" label="Perlu Divalidasi" />
        <StatCard :icon="Calendar"       color="blue"  :value="data.jadwal.length"       label="Jadwal Aktif" />
        <StatCard :icon="Users"          color="green" :value="data.nilai.length"        label="Data Nilai" />
        <StatCard :icon="BookOpen"       color="red"   :value="data.modul.length"        label="Modul Diunggah" />
      </div>
      <div class="grid grid-2">
        <div class="card">
          <div class="card-header">
            <div class="card-title">
              <ShieldCheck class="icon-sm" />
              Validasi Peminjaman
              <span class="badge badge-amber ml-1">{{ peminjamanPending.length }} pending</span>
            </div>
            <router-link to="/peminjaman" class="btn btn-primary btn-sm">Lihat</router-link>
          </div>
          <table><thead><tr>
            <th><User class="icon-xs" /> Pemohon</th>
            <th><Package class="icon-xs" /> Aset</th>
            <th><Activity class="icon-xs" /> Status</th>
          </tr></thead>
            <tbody>
              <tr v-for="p in peminjamanPending.slice(0,3)" :key="p.id">
                <td>{{ p.user?.nama ?? '-' }}</td>
                <td>{{ p.inventaris?.nama ?? '-' }}</td>
                <td><span :class="badgeClass(p.status)">{{ p.status }}</span></td>
              </tr>
              <tr v-if="!peminjamanPending.length">
                <td colspan="3" class="empty-cell"><CheckCircle class="empty-icon-inline" /> Tidak ada pending.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="card">
          <div class="card-header">
            <div class="card-title"><TriangleAlert class="icon-sm" /> Riwayat Kerusakan Aset</div>
            <router-link to="/kerusakan" class="btn btn-outline btn-sm">Lihat</router-link>
          </div>
          <table><thead><tr>
            <th><Package class="icon-xs" /> Nama Aset</th>
            <th><Activity class="icon-xs" /> Status</th>
          </tr></thead>
            <tbody>
              <tr v-for="k in data.kerusakan.slice(0,4)" :key="k.id">
                <td><b>{{ k.inventaris?.nama ?? '-' }}</b></td>
                <td><span :class="badgeClass(k.status)">{{ k.status }}</span></td>
              </tr>
              <tr v-if="!data.kerusakan.length">
                <td colspan="2" class="empty-cell"><ShieldCheck class="empty-icon-inline" /> Tidak ada laporan.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <!-- ── ADMIN ── -->
    <template v-else>
      <div class="page-heading">
        <LayoutDashboard class="heading-icon" />
        <div>
          <h1>Dashboard Admin Pengelola</h1>
          <p>Pantau seluruh aktivitas sistem dari satu tempat.</p>
        </div>
      </div>
      <div class="grid grid-4 mb-6">
        <StatCard :icon="Users"         color="blue"  :value="data.users?.length ?? 0"                              label="Total Pengguna" />
        <StatCard :icon="Briefcase"     color="green" :value="data.inventaris.length"                               label="Total Aset" />
        <StatCard :icon="Wrench"        color="amber" :value="data.kerusakan.filter(k=>k.status!=='Selesai').length" label="Kerusakan Aktif" />
        <StatCard :icon="DoorOpen"      color="red"   :value="data.ruangan?.length ?? 0"                            label="Ruangan Lab" />
      </div>
      <div class="grid grid-2">
        <div class="card">
          <div class="card-header">
            <div class="card-title"><Users class="icon-sm" /> Pengguna Aktif per Peran</div>
            <router-link to="/users" class="btn btn-outline btn-sm">Kelola</router-link>
          </div>
          <div v-for="r in ROLES" :key="r.key" class="role-row">
            <div class="role-label-wrap">
              <component :is="r.icon" class="role-icon" />
              <span class="role-label">{{ r.label }}</span>
            </div>
            <span class="badge badge-blue">{{ countRole(r.key) }}</span>
          </div>
        </div>
        <div class="card">
          <div class="card-header">
            <div class="card-title"><TriangleAlert class="icon-sm" /> Laporan Kerusakan Terbaru</div>
            <router-link to="/kerusakan" class="btn btn-outline btn-sm">Lihat</router-link>
          </div>
          <div v-for="k in data.kerusakan.slice(0,5)" :key="k.id" class="list-item">
            <div class="list-item-inner">
              <Wrench class="list-icon" />
              <div>
                <div class="list-title">{{ k.inventaris?.nama ?? k.deskripsi }}</div>
                <div class="list-sub"><Activity class="icon-xs" /> {{ k.status }}</div>
              </div>
            </div>
          </div>
          <div v-if="!data.kerusakan.length" class="empty-state">
            <ShieldCheck class="empty-icon" /><p>Tidak ada laporan kerusakan.</p>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, defineComponent, h } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useDataStore } from '@/stores/data'
import {
  Briefcase, Clock, BookOpen, Bell, MailOpen, FileText, FolderOpen,
  Calendar, CalendarDays, CalendarOff, CalendarCheck, CalendarCog,
  BarChart3, CheckCheck, ClipboardCheck, ClipboardList, ClipboardX,
  Smile, AlertTriangle, TriangleAlert, Wrench, Zap, Users, User,
  UserCheck, GraduationCap, FlaskConical, LayoutDashboard,
  Package, PackageSearch, PackageX, DoorOpen, Activity, ShieldCheck,
  Upload, Plus, CheckCircle, Zap as ZapIcon,
} from 'lucide-vue-next'

const auth = useAuthStore()
const data = useDataStore()

const role      = computed(() => auth.user?.role ?? '')
const firstName = computed(() => (auth.user?.nama ?? '').split(' ')[0])

const myPinjam          = computed(() => data.peminjaman.filter(p => p.user_id === auth.user?.id))
const myPinjamPending   = computed(() => myPinjam.value.filter(p => p.status?.includes('Menunggu')))
const peminjamanPending = computed(() => data.peminjaman.filter(p => p.status === 'Menunggu Validasi'))

const ROLES = [
  { key: 'mahasiswa', label: 'Mahasiswa',          icon: GraduationCap },
  { key: 'asprak',    label: 'Asisten Praktikum',  icon: UserCheck },
  { key: 'aslab',     label: 'Asisten Lab',        icon: FlaskConical },
  { key: 'dosen',     label: 'Dosen',              icon: BookOpen },
  { key: 'admin',     label: 'Admin',              icon: ShieldCheck },
]

function countRole(r) {
  return (data.users ?? []).filter(u => u.role === r).length
}

function badgeClass(status) {
  if (!status) return 'badge'
  const s = status.toLowerCase()
  if (s.includes('disetujui') || s.includes('selesai') || s.includes('tersedia')) return 'badge badge-green'
  if (s.includes('menunggu') || s.includes('dijadwalkan') || s.includes('berjalan')) return 'badge badge-amber'
  if (s.includes('ditolak') || s.includes('rusak')) return 'badge badge-red'
  return 'badge badge-blue'
}
function badgeKondisi(k) {
  if (k === 'Baik') return 'badge badge-green'
  if (k === 'Rusak Ringan') return 'badge badge-amber'
  return 'badge badge-red'
}

// ─── StatCard inline component ───
const StatCard = defineComponent({
  props: { icon: [String, Object], color: String, value: Number, label: String },
  setup(props) {
    return () => h('div', { class: 'stat-card' }, [
      h('div', { class: `stat-icon ${props.color}` }, [h(props.icon, { size: 22 })]),
      h('div', {}, [
        h('div', { class: 'stat-value' }, String(props.value ?? 0)),
        h('div', { class: 'stat-label' }, props.label),
      ]),
    ])
  },
})

onMounted(async () => {
  await Promise.all([
    data.fetchInventaris?.().catch(()=>{}),
    data.fetchPeminjaman?.().catch(()=>{}),
    data.fetchKerusakan?.().catch(()=>{}),
    data.fetchInsiden?.().catch(()=>{}),
    data.fetchJadwal?.().catch(()=>{}),
    data.fetchModul?.().catch(()=>{}),
    data.fetchAbsensi?.().catch(()=>{}),
    data.fetchNilai?.().catch(()=>{}),
    data.fetchPemeliharaan?.().catch(()=>{}),
    data.fetchRuangan?.().catch(()=>{}),
    data.fetchUsers?.().catch(()=>{}),
  ])
})
</script>

<style scoped>
/* ── Layout ── */
.page-heading { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 24px; }
.page-heading h1 { font-size: 22px; font-weight: 700; color: #0f1f3d; margin-bottom: 4px; display: flex; align-items: center; gap: 8px; }
.page-heading p  { font-size: 14px; color: #64748b; }
.heading-icon { width: 28px; height: 28px; color: #2563eb; margin-top: 3px; flex-shrink: 0; }
.inline-icon  { width: 22px; height: 22px; color: #f59e0b; display: inline; vertical-align: text-bottom; }

.grid { display: grid; gap: 20px; }
.grid-2 { grid-template-columns: repeat(2, 1fr); }
.grid-4 { grid-template-columns: repeat(4, 1fr); }
.mb-6   { margin-bottom: 24px; }
.ml-1   { margin-left: 6px; }

/* ── Stat Card ── */
.stat-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 16px rgba(15,31,61,.08); display: flex; align-items: flex-start; gap: 16px; }
.stat-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-icon.blue  { background: #dbeafe; color: #2563eb; }
.stat-icon.green { background: #d1fae5; color: #059669; }
.stat-icon.amber { background: #fef3c7; color: #d97706; }
.stat-icon.red   { background: #fee2e2; color: #dc2626; }
.stat-value { font-size: 28px; font-weight: 700; color: #0f1f3d; line-height: 1; }
.stat-label { font-size: 12px; color: #64748b; margin-top: 4px; }

/* ── Card ── */
.card { background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 16px rgba(15,31,61,.08); }
.card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.card-title { font-size: 15px; font-weight: 700; color: #0f1f3d; display: flex; align-items: center; gap: 7px; }

/* ── Table ── */
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th { text-align: left; padding: 8px 10px; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; white-space: nowrap; }
td { padding: 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.empty-cell { text-align: center; color: #94a3b8; padding: 20px; display: flex; align-items: center; justify-content: center; gap: 6px; }
.empty-icon-inline { width: 16px; height: 16px; }

/* ── Badge ── */
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-green { background: #d1fae5; color: #065f46; }
.badge-amber { background: #fef3c7; color: #92400e; }
.badge-red   { background: #fee2e2; color: #991b1b; }
.badge-blue  { background: #dbeafe; color: #1e40af; }

/* ── Btn ── */
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: all .2s; }
.btn-primary { background: #0f1f3d; color: #fff; }
.btn-primary:hover { background: #1a3260; }
.btn-success { background: #059669; color: #fff; }
.btn-success:hover { background: #10b981; }
.btn-warning { background: #d97706; color: #fff; }
.btn-warning:hover { background: #f59e0b; }
.btn-outline { background: transparent; color: #0f1f3d; border: 2px solid #e2e8f0; }
.btn-outline:hover { border-color: #0f1f3d; }
.btn-sm { padding: 6px 12px; font-size: 12px; }

/* ── Quick Actions ── */
.quick-actions { display: flex; flex-direction: column; gap: 10px; }
.quick-actions .btn { justify-content: flex-start; padding: 12px 16px; }

/* ── Modul item ── */
.modul-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
.modul-icon { width: 36px; height: 36px; background: #fef3c7; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.modul-icon-svg { width: 18px; height: 18px; color: #d97706; }
.modul-info .modul-title { font-size: 13px; font-weight: 600; color: #0f1f3d; }
.modul-info .modul-sub   { font-size: 11px; color: #64748b; margin-top: 2px; }

/* ── List item ── */
.list-item { border-bottom: 1px solid #f1f5f9; }
.list-item-inner { display: flex; align-items: flex-start; gap: 10px; padding: 10px 0; }
.list-icon { width: 16px; height: 16px; color: #94a3b8; margin-top: 2px; flex-shrink: 0; }
.list-title { font-size: 13px; font-weight: 600; color: #0f1f3d; }
.list-sub   { display: flex; align-items: center; gap: 4px; font-size: 12px; color: #64748b; margin-top: 2px; }

/* ── Role row ── */
.role-row { display: flex; justify-content: space-between; align-items: center; padding: 9px 0; border-bottom: 1px solid #f1f5f9; }
.role-label-wrap { display: flex; align-items: center; gap: 8px; }
.role-icon  { width: 15px; height: 15px; color: #64748b; }
.role-label { font-size: 13px; font-weight: 600; color: #0f1f3d; }

/* ── Empty state ── */
.empty-state { display: flex; flex-direction: column; align-items: center; gap: 8px; text-align: center; padding: 30px 20px; color: #94a3b8; }
.empty-icon  { width: 32px; height: 32px; color: #cbd5e1; }

/* ── Icon sizes ── */
.icon-xs  { width: 13px; height: 13px; }
.icon-sm  { width: 16px; height: 16px; }
.icon-btn { width: 15px; height: 15px; }
</style>