<template>
  <div>
    <div class="page-heading">
      <h1>Data Ruangan</h1>
      <p>Daftar ruangan dan laboratorium yang tersedia.</p>
    </div>

    <div v-if="canEdit" style="margin-bottom:16px">
      <button class="btn btn-primary" @click="openTambah">
        <Plus :size="15" /> Tambah Ruangan
      </button>
    </div>

    <div class="grid-3">
      <div v-for="r in data.ruangan" :key="r.id" class="card">
        <div class="room-icon">
          <Building2 :size="28" />
        </div>
        <div class="room-name">{{ r.nama }}</div>
        <div class="room-lokasi">
          <MapPin :size="12" /> {{ r.lokasi }}
        </div>
        <div class="room-kapasitas">
          <Users :size="12" /> Kapasitas: <b>{{ r.kapasitas }} orang</b>
        </div>
        <span :class="badgeStatus(r.status)">
          <component
            :is="r.status === 'Tersedia' ? CheckCircle2 : r.status === 'Terpakai' ? Clock : XCircle"
            :size="11"
          />
          {{ r.status }}
        </span>
        <div v-if="canEdit" class="room-actions">
          <button class="btn btn-sm btn-outline" @click="openEdit(r)">
            <Pencil :size="13" /> Edit
          </button>
          <div class="status-field">
            <select
              :value="statusSelection[r.id]"
              @change="changeStatus(r, $event.target.value)"
              :disabled="loadingStatus[r.id]"
            >
              <option value="Tersedia">Tersedia</option>
              <option value="Terpakai">Terpakai</option>
              <option value="Tidak Tersedia">Tidak Tersedia</option>
            </select>
            <Loader2 v-if="loadingStatus[r.id]" :size="12" class="spin status-spin" />
          </div>
          <button class="btn btn-sm btn-danger" @click="hapus(r.id)">
            <Trash2 :size="13" />
          </button>
        </div>
      </div>

      <div v-if="!data.ruangan?.length" class="empty-state">
        <Building2 :size="36" class="empty-icon" />
        <p>Belum ada data ruangan.</p>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="modal" class="modal-mask" @click.self="modal = null">
      <div class="modal">
        <div class="modal-header">
          <h3>
            <DoorOpen :size="18" />
            {{ modal.id ? 'Edit Ruangan' : 'Tambah Ruangan' }}
          </h3>
          <button class="close-btn" @click="modal = null"><X :size="18" /></button>
        </div>
        <div class="form-group">
          <label><Building2 :size="12" /> Nama Ruangan</label>
          <input v-model="modal.nama" placeholder="Lab Sistem Informasi" />
        </div>
        <div class="form-row-2">
          <div class="form-group">
            <label><MapPin :size="12" /> Lokasi / Gedung</label>
            <input v-model="modal.lokasi" placeholder="T4 Lt. 2" />
          </div>
          <div class="form-group">
            <label><Users :size="12" /> Kapasitas</label>
            <input v-model.number="modal.kapasitas" type="number" min="1" placeholder="30" />
          </div>
        </div>
        <div class="form-group">
          <label><FileText :size="12" /> Keterangan</label>
          <textarea v-model="modal.keterangan" rows="2" placeholder="Keterangan tambahan (opsional)"></textarea>
        </div>
        <div class="modal-actions">
          <button class="btn btn-outline" @click="modal = null">
            <X :size="14" /> Batal
          </button>
          <button class="btn btn-primary" @click="simpan" :disabled="loading">
            <Loader2 v-if="loading" :size="14" class="spin" />
            <Save v-else :size="14" />
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
  Building2,
  DoorOpen,
  MapPin,
  Users,
  Plus,
  Pencil,
  Trash2,
  CheckCircle2,
  Clock,
  XCircle,
  FileText,
  Save,
  Loader2,
  X,
} from 'lucide-vue-next'

const data          = useDataStore()
const auth          = useAuthStore()
const modal         = ref(null)
const loading       = ref(false)

// statusSelection: { [id]: status } — plain object, bukan nested .value
const statusSelection = ref({})
// loadingStatus: { [id]: boolean } — untuk disable select saat request berlangsung
const loadingStatus   = ref({})

const canEdit = computed(() => ['aslab', 'admin'].includes(auth.role))

// ─── Sync statusSelection dari data store (selalu overwrite) ───────────────
function syncStatusSelection() {
  data.ruangan?.forEach(r => {
    statusSelection.value[r.id] = r.status
  })
}

// ─── Modal ─────────────────────────────────────────────────────────────────
function openTambah() {
  modal.value = { nama: '', lokasi: '', kapasitas: 30, keterangan: '', status: 'Tersedia' }
}
function openEdit(r) {
  modal.value = { ...r }
}

// ─── Simpan (tambah / edit) ─────────────────────────────────────────────────
async function simpan() {
  if (!modal.value.nama?.trim()) {
    alert('Nama ruangan wajib diisi.')
    return
  }
  loading.value = true
  try {
    if (modal.value.id) {
      await api.put(`/ruangan/${modal.value.id}`, modal.value)
    } else {
      await api.post('/ruangan', modal.value)
    }
    await data.fetchRuangan()
    syncStatusSelection()
    modal.value = null
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menyimpan')
  } finally {
    loading.value = false
  }
}

// ─── Ganti status via dropdown ──────────────────────────────────────────────
async function changeStatus(r, newStatus) {
  if (!newStatus || newStatus === r.status) return

  // Optimistic update UI
  statusSelection.value[r.id] = newStatus
  loadingStatus.value[r.id]   = true

  try {
    await api.put(`/ruangan/${r.id}`, { status: newStatus })
    await data.fetchRuangan()
    syncStatusSelection()
  } catch (e) {
    // Rollback jika gagal
    statusSelection.value[r.id] = r.status
    alert(e.response?.data?.message || 'Gagal mengubah status ruangan')
  } finally {
    loadingStatus.value[r.id] = false
  }
}

// ─── Hapus ──────────────────────────────────────────────────────────────────
async function hapus(id) {
  if (!confirm('Hapus ruangan ini?')) return
  try {
    await api.delete(`/ruangan/${id}`)
    await data.fetchRuangan()
    syncStatusSelection()
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menghapus ruangan')
  }
}

// ─── Badge helper ────────────────────────────────────────────────────────────
function badgeStatus(s) {
  if (s === 'Tersedia') return 'badge badge-green'
  if (s === 'Terpakai') return 'badge badge-amber'
  return 'badge badge-red'
}

// ─── Init ────────────────────────────────────────────────────────────────────
onMounted(async () => {
  await data.fetchRuangan?.().catch(() => {})
  syncStatusSelection()
})
</script>

<style scoped>
.page-heading { margin-bottom: 20px; }
.page-heading h1 { font-size: 22px; font-weight: 700; color: #0f1f3d; margin-bottom: 4px; }
.page-heading p  { font-size: 14px; color: #64748b; }

.grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }

.card {
  background: #fff;
  border-radius: 10px;
  padding: 24px;
  box-shadow: 0 2px 16px rgba(15,31,61,.08);
}

.room-icon {
  width: 52px; height: 52px;
  background: #eff6ff; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  color: #2563eb; margin-bottom: 14px;
}
.room-name      { font-size: 16px; font-weight: 700; color: #0f1f3d; margin-bottom: 6px; }
.room-lokasi    { display: flex; align-items: center; gap: 4px; font-size: 13px; color: #64748b; margin-bottom: 4px; }
.room-kapasitas { display: flex; align-items: center; gap: 4px; font-size: 13px; margin-bottom: 12px; color: #0f1f3d; }
.room-actions   { display: flex; gap: 6px; margin-top: 14px; flex-wrap: wrap; align-items: center; }

/* Badge */
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-green { background: #d1fae5; color: #065f46; }
.badge-amber { background: #fef3c7; color: #92400e; }
.badge-red   { background: #fee2e2; color: #991b1b; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 5px; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all .2s; }
.btn:disabled { opacity: .6; cursor: not-allowed; }
.btn-primary { background: #0f1f3d; color: #fff; }
.btn-primary:hover:not(:disabled) { background: #1a3260; }
.btn-outline { background: transparent; border: 2px solid #e2e8f0; color: #0f1f3d; }
.btn-outline:hover { border-color: #0f1f3d; }
.btn-danger  { background: #fee2e2; color: #991b1b; border: none; }
.btn-danger:hover { background: #fecaca; }
.btn-sm { padding: 5px 10px; font-size: 12px; }

/* Status dropdown */
.status-field {
  position: relative;
  display: flex;
  align-items: center;
}
.status-field select {
  padding: 5px 28px 5px 8px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  font-family: inherit;
  color: #0f1f3d;
  background: #f8fafc;
  cursor: pointer;
  outline: none;
  appearance: none;
  transition: border-color .2s, background .2s;
}
.status-field select:hover:not(:disabled) { border-color: #94a3b8; }
.status-field select:focus                { border-color: #2563eb; background: #fff; }
.status-field select:disabled             { opacity: .6; cursor: not-allowed; }
.status-spin {
  position: absolute;
  right: 6px;
  color: #64748b;
  pointer-events: none;
}

/* Modal */
.modal-mask { position: fixed; inset: 0; background: rgba(0,0,0,.4); display: flex; align-items: center; justify-content: center; z-index: 300; }
.modal { background: #fff; border-radius: 12px; padding: 28px; width: 480px; box-shadow: 0 8px 32px rgba(15,31,61,.2); }
.modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.modal-header h3 { display: flex; align-items: center; gap: 8px; font-size: 18px; font-weight: 700; color: #0f1f3d; margin: 0; }
.close-btn { background: none; border: none; cursor: pointer; color: #94a3b8; display: flex; padding: 4px; border-radius: 6px; transition: color .2s, background .2s; }
.close-btn:hover { color: #0f1f3d; background: #f1f5f9; }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-group { margin-bottom: 14px; }
.form-group label { display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: #0f1f3d; margin-bottom: 5px; }
.form-group input,
.form-group textarea { width: 100%; padding: 9px 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; font-family: inherit; outline: none; box-sizing: border-box; }
.form-group input:focus,
.form-group textarea:focus { border-color: #2563eb; }
.modal-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px; }

/* Empty state */
.empty-state { grid-column: 1/-1; text-align: center; padding: 40px; color: #94a3b8; }
.empty-icon  { margin: 0 auto 12px; display: block; opacity: .3; }
.empty-state p { font-size: 13px; }

@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin .8s linear infinite; }
</style>
