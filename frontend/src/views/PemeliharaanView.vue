<template>
  <div>
    <div class="page-heading"><h1>Jadwal Pemeliharaan</h1></div>

    <div v-if="canEdit" class="card mb-4">
      <div class="card-title mb-4">
        <CalendarPlus :size="16" /> Tambah Jadwal Pemeliharaan
      </div>
      <div class="form-row">
        <div class="form-group">
          <label><Wrench :size="12" /> Aset / Area</label>
          <input type="text" v-model="form.alat" placeholder="Semua Komputer Lab RPL" />
        </div>
        <div class="form-group">
          <label><HardHat :size="12" /> Teknisi / PIC</label>
          <input type="text" v-model="form.petugas" placeholder="Tim IT Kampus" />
        </div>
      </div>
      <div class="form-group">
        <label><CalendarDays :size="12" /> Tanggal</label>
        <input type="date" v-model="form.tanggal" style="max-width:220px" />
      </div>
      <button class="btn btn-primary" @click="simpan">
        <Save :size="15" /> Simpan Jadwal
      </button>
    </div>

    <div class="card">
      <div class="card-title mb-4">
        <ClipboardList :size="16" /> Jadwal Aktif
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Aset</th><th>Tanggal</th><th>Petugas</th><th>Status</th>
              <th v-if="canEdit">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in data.pemeliharaan" :key="p.id">
              <td><b>{{ p.alat }}</b></td>
              <td>
                <span class="td-date"><CalendarDays :size="12" /> {{ p.tanggal }}</span>
              </td>
              <td>
                <span class="td-petugas"><HardHat :size="12" /> {{ p.petugas ?? '-' }}</span>
              </td>
              <td>
                <span class="badge" :class="p.status === 'Selesai' ? 'badge-green' : 'badge-amber'">
                  <CheckCircle2 v-if="p.status === 'Selesai'" :size="11" />
                  <Clock v-else :size="11" />
                  {{ p.status }}
                </span>
              </td>
              <td v-if="canEdit">
                <button v-if="p.status !== 'Selesai'" class="btn btn-sm btn-success" @click="selesai(p.id)">
                  <Check :size="14" /> Selesai
                </button>
              </td>
            </tr>
            <tr v-if="data.pemeliharaan.length === 0">
              <td colspan="5" class="empty-cell">
                <CalendarX :size="28" style="margin-bottom:8px;opacity:.3" /><br>
                Belum ada jadwal pemeliharaan
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, onMounted, computed } from 'vue'
import api from '@/api'
import { useDataStore } from '@/stores/data'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'
import {
  CalendarPlus,
  CalendarDays,
  CalendarX,
  ClipboardList,
  Wrench,
  HardHat,
  Save,
  Check,
  CheckCircle2,
  Clock,
} from 'lucide-vue-next'

const data = useDataStore(); const auth = useAuthStore(); const ui = useUiStore()
const canEdit = computed(() => ['aslab', 'admin'].includes(auth.role))

const today = new Date().toISOString().slice(0, 10)
const form = reactive({ alat: '', tanggal: today, petugas: '' })

onMounted(() => data.fetchPemeliharaan().catch(() => {}))

async function simpan() {
  if (!form.alat || !form.tanggal) {
    ui.showError('Gagal', 'Lengkapi nama aset dan tanggal!')
    return
  }
  try {
    await api.post('/pemeliharaan', { alat: form.alat, tanggal: form.tanggal, petugas: form.petugas })
    await data.fetchPemeliharaan()
    ui.showSuccess('Berhasil', 'Jadwal pemeliharaan ditambahkan.')
    Object.assign(form, { alat: '', tanggal: today, petugas: '' })
  } catch {
    ui.showError('Gagal', 'Gagal menyimpan jadwal.')
  }
}

async function selesai(id) {
  try {
    await api.post(`/pemeliharaan/${id}/selesai`)
    await data.fetchPemeliharaan()
    ui.showSuccess('Berhasil', 'Pemeliharaan ditandai selesai.')
  } catch {
    ui.showError('Gagal', 'Gagal memperbarui status.')
  }
}
</script>

<style scoped>
.page-heading { margin-bottom: 20px; }
.page-heading h1 { font-size: 22px; font-weight: 700; color: #0f1f3d; }

.card { background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 16px rgba(15,31,61,.08); }
.card-title { display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 700; color: #0f1f3d; }
.mb-4 { margin-bottom: 16px; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-group { margin-bottom: 14px; }
.form-group label { display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: #0f1f3d; margin-bottom: 5px; }
.form-group input { width: 100%; padding: 9px 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; font-family: inherit; outline: none; box-sizing: border-box; }
.form-group input:focus { border-color: #2563eb; }

.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th { text-align: left; padding: 8px 10px; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
td { padding: 11px 10px; border-bottom: 1px solid #f1f5f9; }

.td-date,
.td-petugas { display: inline-flex; align-items: center; gap: 5px; color: #475569; }

.badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-green { background: #d1fae5; color: #065f46; }
.badge-amber { background: #fef3c7; color: #92400e; }

.empty-cell { text-align: center; color: #94a3b8; padding: 36px; line-height: 1.8; }

.btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all .2s; }
.btn-primary { background: #0f1f3d; color: #fff; }
.btn-primary:hover { background: #1a3260; }
.btn-sm { padding: 5px 10px; font-size: 12px; }
.btn-success { background: #d1fae5; color: #065f46; }
.btn-success:hover { background: #a7f3d0; }
</style>