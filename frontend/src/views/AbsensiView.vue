<template>
  <div>
    <div class="page-heading">
      <component :is="auth.role === 'dosen' ? 'ClipboardList' : 'ClipboardCheck'" class="heading-icon" />
      <h1>{{ auth.role === 'dosen' ? 'Riwayat Absensi Praktikum' : 'Absensi Praktikum' }}</h1>
    </div>

    <!-- Form input absensi (asprak only) -->
    <div v-if="canEdit" class="card mb-4">
      <div class="card-title mb-4">
        <PlusCircle class="icon-sm" />
        Input Absensi
      </div>
      <div class="form-row">
        <div class="form-group">
          <label><BookOpen class="icon-xs" /> Mata Kuliah</label>
          <div class="input-wrap">
            <select v-model="form.matkul">
              <option value="">-- Pilih Mata Kuliah --</option>
              <option v-for="m in data.matkul" :key="m.id" :value="m.nama">{{ m.nama }}</option>
            </select>
            <ChevronDown class="select-arrow" />
          </div>
        </div>
        <div class="form-group">
          <label><User class="icon-xs" /> Nama Mahasiswa</label>
          <div class="input-wrap">
            <UserSearch class="input-icon" />
            <input type="text" v-model="form.mahasiswa" placeholder="Nama mahasiswa" class="has-icon" />
          </div>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label><CalendarDays class="icon-xs" /> Tanggal</label>
          <div class="input-wrap">
            <Calendar class="input-icon" />
            <input type="date" v-model="form.tanggal" class="has-icon" />
          </div>
        </div>
        <div class="form-group">
          <label><CheckCircle class="icon-xs" /> Status Kehadiran</label>
          <div class="input-wrap">
            <select v-model="form.status">
              <option value="Hadir">Hadir</option>
              <option value="Izin">Izin</option>
              <option value="Alfa">Alfa</option>
            </select>
            <ChevronDown class="select-arrow" />
          </div>
        </div>
      </div>
      <button class="btn btn-primary" @click="simpanAbsensi">
        <Save class="icon-btn" />
        Simpan Absensi
      </button>
    </div>

    <!-- Tabel riwayat -->
    <div class="card">
      <div class="card-title mb-4">
        <History class="icon-sm" />
        Riwayat Absensi
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th><CalendarDays class="icon-xs" /> Tanggal</th>
              <th><BookOpen class="icon-xs" /> Mata Kuliah</th>
              <th><User class="icon-xs" /> Mahasiswa</th>
              <th><Activity class="icon-xs" /> Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="a in data.absensi" :key="a.id">
              <td>{{ a.tanggal }}</td>
              <td><b>{{ a.matkul }}</b></td>
              <td>{{ a.mahasiswa }}</td>
              <td>
                <span class="badge" :class="a.status === 'Hadir' ? 'badge-green' : a.status === 'Izin' ? 'badge-amber' : 'badge-red'">
                  <component :is="a.status === 'Hadir' ? 'CircleCheck' : a.status === 'Izin' ? 'Clock' : 'CircleX'" class="badge-icon" />
                  {{ a.status }}
                </span>
              </td>
            </tr>
            <tr v-if="data.absensi.length === 0">
              <td colspan="4" class="empty-state">
                <ClipboardX class="empty-icon" />
                <span>Belum ada data absensi</span>
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
  ClipboardList, ClipboardCheck, ClipboardX,
  PlusCircle, BookOpen, User, UserSearch,
  CalendarDays, Calendar, CheckCircle, ChevronDown,
  Save, History, Activity,
  CircleCheck, CircleX, Clock
} from 'lucide-vue-next'

const data = useDataStore(); const auth = useAuthStore(); const ui = useUiStore()
const canEdit = computed(() => auth.role === 'asprak')

const today = new Date().toISOString().slice(0, 10)
const form = reactive({ matkul: '', mahasiswa: '', tanggal: today, status: 'Hadir' })

onMounted(() => {
  data.fetchAbsensi().catch(() => {})
  data.fetchMatkul?.().catch(() => {})
})

async function simpanAbsensi() {
  if (!form.matkul || !form.mahasiswa) {
    ui.showError('Gagal', 'Lengkapi mata kuliah dan nama mahasiswa!')
    return
  }
  try {
    await api.post('/absensi', { ...form })
    await data.fetchAbsensi()
    ui.showSuccess('Berhasil', 'Absensi berhasil disimpan.')
    Object.assign(form, { matkul: '', mahasiswa: '', tanggal: today, status: 'Hadir' })
  } catch {
    ui.showError('Gagal', 'Gagal menyimpan absensi.')
  }
}
</script>

<style scoped>
/* Layout */
.page-heading { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
.page-heading h1 { font-size: 22px; font-weight: 700; color: #0f1f3d; }
.heading-icon { width: 26px; height: 26px; color: #2563eb; flex-shrink: 0; }

.card { background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 16px rgba(15,31,61,.08); }
.card-title { font-size: 15px; font-weight: 700; color: #0f1f3d; display: flex; align-items: center; gap: 7px; }
.mb-4 { margin-bottom: 16px; }

/* Form */
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-group { margin-bottom: 14px; }
.form-group label {
  display: flex; align-items: center; gap: 5px;
  font-size: 12px; font-weight: 700; color: #0f1f3d; margin-bottom: 5px;
}

/* Input wrappers */
.input-wrap { position: relative; }
.input-wrap input,
.input-wrap select {
  width: 100%; padding: 9px 12px; border: 2px solid #e2e8f0;
  border-radius: 8px; font-size: 14px; font-family: inherit; outline: none;
  appearance: none; -webkit-appearance: none; background: #fff;
  box-sizing: border-box;
}
.input-wrap input.has-icon,
.input-wrap select.has-icon { padding-left: 36px; }
.input-wrap input:focus,
.input-wrap select:focus { border-color: #2563eb; }

.input-icon {
  position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
  width: 15px; height: 15px; color: #94a3b8; pointer-events: none;
}
.select-arrow {
  position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
  width: 15px; height: 15px; color: #94a3b8; pointer-events: none;
}

/* Table */
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th {
  text-align: left; padding: 8px 10px; color: #64748b;
  font-size: 11px; font-weight: 700; text-transform: uppercase;
  border-bottom: 2px solid #e2e8f0;
}
th { display: revert; /* override if needed */ }
/* Use flex inside th via a wrapper if needed — simpler: just inline-flex the content */
th { vertical-align: middle; }
td { padding: 11px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

/* Badge */
.badge {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
}
.badge-icon { width: 12px; height: 12px; }
.badge-green { background: #d1fae5; color: #065f46; }
.badge-amber { background: #fef3c7; color: #92400e; }
.badge-red   { background: #fee2e2; color: #991b1b; }

/* Empty state */
.empty-state { text-align: center; color: #94a3b8; padding: 28px; }
.empty-state { display: flex; flex-direction: column; align-items: center; gap: 8px; }
.empty-icon { width: 32px; height: 32px; color: #cbd5e1; }

/* Button */
.btn {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 9px 16px; border-radius: 8px; font-size: 13px;
  font-weight: 600; cursor: pointer; border: none; transition: all .2s;
}
.btn-primary { background: #0f1f3d; color: #fff; }
.btn-primary:hover { background: #1a3260; }

/* Icon sizes */
.icon-xs { width: 13px; height: 13px; }
.icon-sm { width: 16px; height: 16px; }
.icon-btn { width: 15px; height: 15px; }
</style>