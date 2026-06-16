<template>
  <div>

    <!-- MAHASISWA / ASPRAK -->
    <template v-if="isMahasiswaOrAsprak">
      <div class="page-heading">
        <h1>Peminjaman Aset</h1>
        <p>Ajukan peminjaman aset laboratorium dan pantau statusnya.</p>
      </div>
      <div class="tab-bar">
        <button :class="['tab', tab === 'ajukan' && 'active']" @click="tab = 'ajukan'">
          <SendHorizonal :size="15" /> Ajukan Peminjaman
        </button>
        <button :class="['tab', tab === 'riwayat' && 'active']" @click="tab = 'riwayat'">
          <ClipboardList :size="15" /> Riwayat Saya
        </button>
      </div>

      <div v-if="tab === 'ajukan'" class="grid grid-2">
        <div class="card">
          <div class="card-title mb-4">
            <FilePlus2 :size="16" /> Form Peminjaman Alat / Aset
          </div>
          <div class="form-group">
            <label><Package :size="12" /> Pilih Aset</label>
            <select v-model="form.inventaris_id">
              <option value="">-- Pilih Aset Tersedia --</option>
              <option v-for="i in asetTersedia" :key="i.id" :value="i.id">
                {{ i.nama }} — {{ i.ruangan?.nama ?? 'tanpa ruangan' }}
              </option>
            </select>
            <div v-if="!asetTersedia.length" class="form-hint">
              <Info :size="11" /> Tidak ada aset tersedia saat ini.
            </div>
          </div>
          <div class="form-row-2">
            <div class="form-group">
              <label><CalendarArrowDown :size="12" /> Tanggal Pinjam</label>
              <input type="date" v-model="form.tgl_pinjam" :min="today" />
            </div>
            <div class="form-group">
              <label><CalendarArrowUp :size="12" /> Tanggal Kembali</label>
              <input type="date" v-model="form.tgl_kembali" :min="form.tgl_pinjam || today" />
            </div>
          </div>
          <div class="form-group">
            <label><MessageSquare :size="12" /> Keperluan</label>
            <textarea v-model="form.keperluan" rows="3" placeholder="Jelaskan keperluan peminjaman..."></textarea>
          </div>
          <button class="btn btn-primary btn-full" @click="ajukan" :disabled="loading || !form.inventaris_id">
            <Loader2 v-if="loading" :size="15" class="spin" />
            <SendHorizonal v-else :size="15" />
            {{ loading ? 'Mengajukan…' : 'Ajukan Peminjaman' }}
          </button>
          <div v-if="successMsg" class="alert-success">
            <CheckCircle2 :size="14" /> {{ successMsg }}
          </div>
          <div v-if="errorMsg" class="alert-error">
            <AlertCircle :size="14" /> {{ errorMsg }}
          </div>
        </div>

        <div class="card">
          <div class="card-title mb-4">
            <LayoutList :size="16" /> Status Aset Tersedia
          </div>
          <div v-for="i in data.inventaris" :key="i.id" class="aset-row">
            <div>
              <div class="aset-nama">{{ i.nama }}</div>
              <div class="aset-sub">{{ i.ruangan?.nama ?? '-' }}</div>
            </div>
            <span :class="badgeStatus(i.status)">{{ i.status }}</span>
          </div>
          <div v-if="!data.inventaris.length" class="empty-state">
            <Package :size="32" /><p>Belum ada aset terdaftar.</p>
          </div>
        </div>
      </div>

      <div v-if="tab === 'riwayat'" class="card">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Aset</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Keperluan</th><th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in myPeminjaman" :key="p.id">
                <td><b>{{ p.inventaris?.nama ?? '-' }}</b></td>
                <td>{{ p.tgl_pinjam }}</td>
                <td>{{ p.tgl_kembali ?? '-' }}</td>
                <td>{{ p.keperluan ?? '-' }}</td>
                <td><span :class="badgeClass(p.status)">{{ p.status }}</span></td>
              </tr>
              <tr v-if="!myPeminjaman.length">
                <td colspan="5" class="empty-cell">
                  <ClipboardX :size="26" style="margin-bottom:6px;opacity:.3" /><br>
                  Belum ada riwayat peminjaman.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <!-- DOSEN — Validasi -->
    <template v-else-if="auth.role === 'dosen'">
      <div class="page-heading">
        <h1>Validasi Peminjaman</h1>
        <p>Periksa dan validasi permintaan peminjaman dari mahasiswa.</p>
      </div>
      <div class="card mb-4">
        <div class="card-title mb-4">
          <ShieldCheck :size="16" /> Menunggu Validasi
          <span class="badge badge-amber" style="margin-left:8px">{{ pending.length }}</span>
        </div>
        <div v-if="!pending.length" class="empty-state">
          <CheckCircle2 :size="32" /><p>Tidak ada peminjaman yang menunggu validasi.</p>
        </div>
        <div v-for="p in pending" :key="p.id" class="validasi-card">
          <div class="validasi-info">
            <div class="validasi-name">
              <UserRound :size="14" style="display:inline;vertical-align:middle;margin-right:4px" />
              {{ p.user?.nama ?? 'Pemohon' }}
              <span class="validasi-nim">{{ p.user?.nim_nip ? `(${p.user.nim_nip})` : '' }}</span>
            </div>
            <div class="validasi-detail">
              <Package :size="12" style="display:inline;vertical-align:middle;margin-right:3px" />
              Aset: <b>{{ p.inventaris?.nama ?? '-' }}</b>
            </div>
            <div class="validasi-sub">
              <CalendarDays :size="11" style="display:inline;vertical-align:middle;margin-right:3px" />
              Pinjam: {{ p.tgl_pinjam }} s/d {{ p.tgl_kembali ?? '—' }}
              <span v-if="p.keperluan"> · {{ p.keperluan }}</span>
            </div>
          </div>
          <div class="validasi-actions">
            <button class="btn btn-success btn-sm" @click="setujui(p.id)">
              <Check :size="14" /> Setujui
            </button>
            <button class="btn btn-danger btn-sm" @click="tolak(p.id)">
              <X :size="14" /> Tolak
            </button>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-title mb-4">
          <History :size="16" /> Riwayat Validasi
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>Pemohon</th><th>Aset</th><th>Tgl Pinjam</th><th>Status</th></tr>
            </thead>
            <tbody>
              <tr v-for="p in sudahDivalidasi" :key="p.id">
                <td>{{ p.user?.nama ?? '-' }}</td>
                <td>{{ p.inventaris?.nama ?? '-' }}</td>
                <td>{{ p.tgl_pinjam }}</td>
                <td><span :class="badgeClass(p.status)">{{ p.status }}</span></td>
              </tr>
              <tr v-if="!sudahDivalidasi.length">
                <td colspan="4" class="empty-cell">Belum ada riwayat validasi.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <!-- ASLAB / ADMIN -->
    <template v-else>
      <div class="page-heading">
        <h1>Semua Peminjaman</h1>
        <p>Seluruh data peminjaman aset laboratorium.</p>
      </div>
      <div class="filter-bar">
        <div class="search-wrap">
          <Search :size="15" class="search-icon" />
          <input v-model="search" class="search-input" placeholder="Cari pemohon / aset..." />
        </div>
        <select v-model="filterStatus" class="select-filter">
          <option value="">Semua Status</option>
          <option>Menunggu Validasi</option>
          <option>Disetujui</option>
          <option>Ditolak</option>
          <option>Selesai</option>
        </select>
      </div>
      <div class="card">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Pemohon</th><th>Aset</th><th>Tgl Pinjam</th><th>Tgl Kembali</th>
                <th>Keperluan</th><th>Status</th><th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in filteredPeminjaman" :key="p.id">
                <td><b>{{ p.user?.nama ?? '-' }}</b></td>
                <td>{{ p.inventaris?.nama ?? '-' }}</td>
                <td>{{ p.tgl_pinjam }}</td>
                <td>{{ p.tgl_kembali ?? '-' }}</td>
                <td>{{ p.keperluan ?? '-' }}</td>
                <td><span :class="badgeClass(p.status)">{{ p.status }}</span></td>
                <td>
                  <button v-if="p.status === 'Disetujui'" class="btn btn-sm btn-outline" @click="kembalikan(p.id)">
                    <PackageCheck :size="13" /> Selesai
                  </button>
                </td>
              </tr>
              <tr v-if="!filteredPeminjaman.length">
                <td colspan="7" class="empty-cell">
                  <PackageX :size="26" style="margin-bottom:6px;opacity:.3" /><br>
                  Tidak ada data peminjaman.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useDataStore } from '@/stores/data'
import { useAuthStore } from '@/stores/auth'
import api from '@/api'
import {
  SendHorizonal,
  ClipboardList,
  ClipboardX,
  FilePlus2,
  Package,
  PackageCheck,
  PackageX,
  LayoutList,
  CalendarArrowDown,
  CalendarArrowUp,
  CalendarDays,
  MessageSquare,
  Info,
  CheckCircle2,
  AlertCircle,
  Loader2,
  ShieldCheck,
  UserRound,
  History,
  Search,
  Check,
  X,
} from 'lucide-vue-next'

const data  = useDataStore()
const auth  = useAuthStore()

const tab          = ref('ajukan')
const search       = ref('')
const filterStatus = ref('')
const loading      = ref(false)
const successMsg   = ref('')
const errorMsg     = ref('')
const today        = new Date().toISOString().slice(0, 10)
const form         = ref({ inventaris_id: '', tgl_pinjam: today, tgl_kembali: '', keperluan: '' })

const isMahasiswaOrAsprak = computed(() => ['mahasiswa', 'asprak'].includes(auth.role))
const asetTersedia        = computed(() => data.inventaris.filter(i => i.status === 'Tersedia'))
const myPeminjaman        = computed(() => data.peminjaman.filter(p => p.user_id === auth.user?.id))
const pending             = computed(() => data.peminjaman.filter(p => p.status === 'Menunggu Validasi'))
const sudahDivalidasi     = computed(() => data.peminjaman.filter(p => ['Disetujui','Ditolak','Selesai'].includes(p.status)))
const filteredPeminjaman  = computed(() => {
  const q = search.value.toLowerCase()
  return data.peminjaman.filter(p => {
    const matchSearch = !q || p.user?.nama?.toLowerCase().includes(q) || p.inventaris?.nama?.toLowerCase().includes(q)
    const matchStatus = !filterStatus.value || p.status === filterStatus.value
    return matchSearch && matchStatus
  })
})

async function ajukan() {
  if (!form.value.inventaris_id) return
  loading.value  = true
  successMsg.value = ''
  errorMsg.value   = ''
  try {
    await api.post('/peminjaman', form.value)
    await data.fetchPeminjaman()
    successMsg.value = 'Peminjaman berhasil diajukan! Menunggu validasi.'
    form.value = { inventaris_id: '', tgl_pinjam: today, tgl_kembali: '', keperluan: '' }
    tab.value = 'riwayat'
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Gagal mengajukan peminjaman.'
  } finally { loading.value = false }
}

async function setujui(id) {
  await api.post(`/peminjaman/${id}/setujui`)
  await data.fetchPeminjaman()
}

async function tolak(id) {
  await api.post(`/peminjaman/${id}/tolak`)
  await data.fetchPeminjaman()
}

async function kembalikan(id) {
  if (!confirm('Tandai peminjaman ini sebagai Selesai?')) return
  await api.post(`/peminjaman/${id}/kembalikan`)
  await data.fetchPeminjaman()
}

function badgeClass(s) {
  if (!s) return 'badge'
  const l = s.toLowerCase()
  if (l.includes('disetujui') || l.includes('selesai')) return 'badge badge-green'
  if (l.includes('menunggu'))  return 'badge badge-amber'
  if (l.includes('ditolak'))   return 'badge badge-red'
  return 'badge badge-blue'
}
function badgeStatus(s) {
  if (!s) return 'badge'
  if (s === 'Tersedia')  return 'badge badge-green'
  if (s === 'Dipinjam')  return 'badge badge-amber'
  return 'badge badge-red'
}

onMounted(async () => {
  await Promise.all([
    data.fetchPeminjaman().catch(() => {}),
    data.fetchInventaris().catch(() => {}),
  ])
})
</script>

<style scoped>
.page-heading { margin-bottom: 20px; }
.page-heading h1 { font-size: 22px; font-weight: 700; color: #0f1f3d; margin-bottom: 4px; }
.page-heading p  { font-size: 14px; color: #64748b; }
.tab-bar { display: flex; gap: 6px; margin-bottom: 20px; }
.tab { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; border: 2px solid #e2e8f0; background: #fff; color: #64748b; cursor: pointer; transition: all .15s; }
.tab.active { background: #0f1f3d; color: #fff; border-color: #0f1f3d; }
.grid-2  { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.mb-4    { margin-bottom: 16px; }
.card { background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 16px rgba(15,31,61,.08); }
.card-title { font-size: 15px; font-weight: 700; color: #0f1f3d; display: flex; align-items: center; gap: 8px; }
.form-group { margin-bottom: 14px; }
.form-group label { display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: #0f1f3d; margin-bottom: 5px; }
.form-group input,
.form-group select,
.form-group textarea { width: 100%; padding: 9px 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; font-family: inherit; outline: none; box-sizing: border-box; }
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus { border-color: #2563eb; }
.form-hint { display: flex; align-items: center; gap: 4px; font-size: 11px; color: #94a3b8; margin-top: 4px; }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.btn-full { width: 100%; justify-content: center; }

/* Search bar */
.filter-bar { display: flex; gap: 10px; margin-bottom: 16px; align-items: center; }
.search-wrap { position: relative; flex: 1; max-width: 320px; display: flex; align-items: center; }
.search-icon { position: absolute; left: 11px; color: #94a3b8; pointer-events: none; }
.search-input { width: 100%; padding: 9px 14px 9px 34px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none; }
.search-input:focus { border-color: #2563eb; }
.select-filter { padding: 9px 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; cursor: pointer; }

.aset-row { display: flex; justify-content: space-between; align-items: center; padding: 9px 0; border-bottom: 1px solid #f1f5f9; }
.aset-nama { font-size: 13px; font-weight: 600; color: #0f1f3d; }
.aset-sub  { font-size: 11px; color: #64748b; margin-top: 2px; }

.validasi-card { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap; background: #f8faff; border-radius: 8px; padding: 16px; margin-bottom: 12px; }
.validasi-name  { font-size: 14px; font-weight: 700; color: #0f1f3d; }
.validasi-nim   { font-size: 12px; color: #64748b; font-weight: 400; }
.validasi-detail{ font-size: 13px; margin-top: 4px; color: #0f1f3d; }
.validasi-sub   { font-size: 12px; color: #64748b; margin-top: 2px; }
.validasi-actions { display: flex; gap: 8px; flex-shrink: 0; }

.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th { text-align: left; padding: 8px 10px; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; white-space: nowrap; }
td { padding: 11px 10px; border-bottom: 1px solid #f1f5f9; }
.empty-cell { text-align: center; color: #94a3b8; padding: 32px; line-height: 1.8; }

.badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-green { background: #d1fae5; color: #065f46; }
.badge-amber { background: #fef3c7; color: #92400e; }
.badge-red   { background: #fee2e2; color: #991b1b; }
.badge-blue  { background: #dbeafe; color: #1e40af; }

.btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all .2s; }
.btn:disabled { opacity: .6; cursor: not-allowed; }
.btn-primary { background: #0f1f3d; color: #fff; }
.btn-primary:hover:not(:disabled) { background: #1a3260; }
.btn-success { background: #d1fae5; color: #065f46; }
.btn-success:hover { background: #a7f3d0; }
.btn-danger  { background: #fee2e2; color: #991b1b; }
.btn-danger:hover  { background: #fecaca; }
.btn-outline { background: transparent; border: 2px solid #e2e8f0; color: #0f1f3d; }
.btn-outline:hover { border-color: #0f1f3d; }
.btn-sm { padding: 6px 12px; font-size: 12px; }

.alert-success { display: flex; align-items: center; gap: 8px; margin-top: 12px; padding: 10px 14px; background: #d1fae5; color: #065f46; border-radius: 8px; font-size: 13px; }
.alert-error   { display: flex; align-items: center; gap: 8px; margin-top: 12px; padding: 10px 14px; background: #fee2e2; color: #991b1b; border-radius: 8px; font-size: 13px; }

.empty-state { text-align: center; padding: 32px 20px; color: #94a3b8; display: flex; flex-direction: column; align-items: center; gap: 8px; }
.empty-state p { font-size: 13px; }

@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin .8s linear infinite; }
</style>