<template>
  <div>
    <div class="page-heading">
      <CalendarClock class="heading-icon" />
      <div>
        <h1>Jadwal Praktikum</h1>
        <p>Daftar jadwal praktikum seluruh mata kuliah.</p>
      </div>
    </div>

    <div v-if="canEdit" style="margin-bottom:16px">
      <button class="btn btn-primary" @click="openTambah">
        <PlusCircle class="icon-btn" />
        Tambah Jadwal
      </button>
    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th><BookOpen class="icon-xs" /> Mata Kuliah</th>
              <th><DoorOpen class="icon-xs" /> Ruang / Lab</th>
              <th><CalendarDays class="icon-xs" /> Hari</th>
              <th><Clock class="icon-xs" /> Jam</th>
              <th><GraduationCap class="icon-xs" /> Dosen</th>
              <th v-if="canEdit"><Settings2 class="icon-xs" /> Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="j in data.jadwal" :key="j.id">
              <td><b>{{ j.matkul }}</b></td>
              <td>{{ j.ruang }}</td>
              <td>{{ j.hari }}</td>
              <td>{{ j.jam }}</td>
              <td>{{ j.dosen }}</td>
              <td v-if="canEdit">
                <button class="btn btn-sm btn-danger" @click="hapus(j.id)">
                  <Trash2 :size="14" />
                </button>
              </td>
            </tr>
            <tr v-if="!data.jadwal.length">
              <td :colspan="canEdit ? 6 : 5" class="empty-cell">
                <CalendarOff class="empty-icon" />
                <span>Belum ada jadwal praktikum.</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="modal" class="modal-mask" @click.self="modal = null">
      <div class="modal">
        <div class="modal-header">
          <CalendarPlus class="modal-header-icon" />
          <h3>Tambah Jadwal</h3>
          <button class="btn-close" @click="modal = null"><X :size="18" /></button>
        </div>

        <div class="form-group">
          <label><BookOpen class="icon-xs" /> Mata Kuliah</label>
          <div class="input-wrap">
            <select v-model="modal.matkul">
              <option value="">-- Pilih Mata Kuliah --</option>
              <option v-for="m in data.matkul" :key="m.id" :value="m.nama">{{ m.nama }}</option>
            </select>
            <ChevronDown class="select-arrow" />
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label><DoorOpen class="icon-xs" /> Ruang / Lab</label>
            <div class="input-wrap">
              <select v-model="modal.ruang">
                <option value="">-- Pilih Ruangan --</option>
                <option v-for="r in data.ruangan" :key="r.id" :value="r.nama">{{ r.nama }}</option>
              </select>
              <ChevronDown class="select-arrow" />
            </div>
          </div>
          <div class="form-group">
            <label><GraduationCap class="icon-xs" /> Dosen</label>
            <div class="input-wrap">
              <UserRound class="input-icon" />
              <input v-model="modal.dosen" placeholder="Nama dosen" class="has-icon" />
            </div>
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label><CalendarDays class="icon-xs" /> Hari</label>
            <div class="input-wrap">
              <select v-model="modal.hari">
                <option>Senin</option><option>Selasa</option><option>Rabu</option>
                <option>Kamis</option><option>Jumat</option><option>Sabtu</option>
              </select>
              <ChevronDown class="select-arrow" />
            </div>
          </div>
          <div class="form-group">
            <label><Clock class="icon-xs" /> Jam</label>
            <div class="input-wrap">
              <Timer class="input-icon" />
              <input v-model="modal.jam" placeholder="08:00 - 10:00" class="has-icon" />
            </div>
          </div>
        </div>

        <div class="modal-actions">
          <button class="btn btn-outline" @click="modal = null">
            <X class="icon-btn" /> Batal
          </button>
          <button class="btn btn-primary" @click="simpan" :disabled="loading">
            <Loader2 v-if="loading" class="icon-btn spinning" />
            <Save v-else class="icon-btn" />
            {{ loading ? 'Menyimpan…' : 'Simpan' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useDataStore } from '@/stores/data'
import { useAuthStore } from '@/stores/auth'
import api from '@/api'
import {
  CalendarClock, CalendarDays, CalendarOff, CalendarPlus,
  BookOpen, DoorOpen, Clock, GraduationCap, Settings2,
  PlusCircle, Trash2, ChevronDown, UserRound, Timer,
  Save, Loader2, X
} from 'lucide-vue-next'

const data    = useDataStore()
const auth    = useAuthStore()
const modal   = ref(null)
const loading = ref(false)

const canEdit = computed(() => auth.role === 'admin')

function openTambah() {
  modal.value = { matkul: '', ruang: '', dosen: '', hari: 'Senin', jam: '' }
}

async function simpan() {
  loading.value = true
  try {
    await api.post('/jadwal', modal.value)
    await data.fetchJadwal()
    modal.value = null
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menyimpan')
  } finally { loading.value = false }
}

async function hapus(id) {
  if (!confirm('Hapus jadwal ini?')) return
  await api.delete(`/jadwal/${id}`)
  await data.fetchJadwal()
}

onMounted(async () => {
  await Promise.all([
    data.fetchJadwal?.().catch(() => {}),
    data.fetchMatkul?.().catch(() => {}),
    data.fetchRuangan?.().catch(() => {}),
  ])
})
</script>

<style scoped>
/* Heading */
.page-heading { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 20px; }
.page-heading h1 { font-size: 22px; font-weight: 700; color: #0f1f3d; margin-bottom: 4px; }
.page-heading p  { font-size: 14px; color: #64748b; }
.heading-icon { width: 28px; height: 28px; color: #2563eb; margin-top: 2px; flex-shrink: 0; }

/* Card & table */
.card { background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 16px rgba(15,31,61,.08); }
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th {
  text-align: left; padding: 8px 10px; color: #64748b;
  font-size: 11px; font-weight: 700; text-transform: uppercase;
  border-bottom: 2px solid #e2e8f0; white-space: nowrap;
}
td { padding: 11px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

/* Empty state */
.empty-cell { text-align: center; color: #94a3b8; padding: 36px !important; }
.empty-cell { display: flex; flex-direction: column; align-items: center; gap: 10px; }
.empty-icon { width: 36px; height: 36px; color: #cbd5e1; }

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

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all .2s; }
.btn:disabled { opacity: .6; cursor: not-allowed; }
.btn-primary { background: #0f1f3d; color: #fff; }
.btn-primary:hover:not(:disabled) { background: #1a3260; }
.btn-outline { background: transparent; border: 2px solid #e2e8f0; color: #0f1f3d; }
.btn-outline:hover { border-color: #0f1f3d; }
.btn-danger  { background: #fee2e2; color: #991b1b; border: none; }
.btn-danger:hover { background: #fecaca; }
.btn-sm { padding: 5px 9px; font-size: 12px; }
.btn-close {
  margin-left: auto; background: none; border: none; cursor: pointer;
  color: #64748b; display: flex; align-items: center; padding: 4px;
  border-radius: 6px; transition: background .15s;
}
.btn-close:hover { background: #f1f5f9; color: #0f1f3d; }

/* Modal */
.modal-mask { position: fixed; inset: 0; background: rgba(0,0,0,.4); display: flex; align-items: center; justify-content: center; z-index: 300; }
.modal { background: #fff; border-radius: 12px; padding: 28px; width: 480px; box-shadow: 0 8px 32px rgba(15,31,61,.2); }
.modal-header { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
.modal-header h3 { font-size: 18px; font-weight: 700; color: #0f1f3d; margin: 0; }
.modal-header-icon { width: 22px; height: 22px; color: #2563eb; flex-shrink: 0; }

/* Form */
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-group { margin-bottom: 14px; }
.form-group label { display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: #0f1f3d; margin-bottom: 5px; }
.modal-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px; }

/* Icon helpers */
.icon-xs  { width: 13px; height: 13px; }
.icon-btn { width: 15px; height: 15px; }

/* Spinner */
.spinning { animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>