<template>
  <div>
    <div class="page-heading">
      <Package class="heading-icon" />
      <div>
        <h1>Inventaris Aset</h1>
        <p>Kelola seluruh aset dan peralatan laboratorium.</p>
      </div>
    </div>

    <!-- Filter bar -->
    <div class="filter-bar">
      <div class="search-wrap">
        <Search class="search-icon" />
        <input v-model="search" class="search-input" type="text" placeholder="Cari aset..." />
      </div>
      <button v-if="canEdit" class="btn btn-primary" @click="openTambah">
        <PlusCircle class="icon-btn" /> Tambah Aset
      </button>
    </div>

    <!-- Tabel -->
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th><Hash class="icon-xs" /> Kode</th>
              <th><Package class="icon-xs" /> Nama Aset</th>
              <th><DoorOpen class="icon-xs" /> Ruangan / Lab</th>
              <th><Tag class="icon-xs" /> Kategori</th>
              <th><Layers class="icon-xs" /> Jumlah</th>
              <th><ShieldCheck class="icon-xs" /> Kondisi</th>
              <th><Activity class="icon-xs" /> Status</th>
              <th v-if="canEdit"><Settings2 class="icon-xs" /> Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="i in filtered" :key="i.id">
              <td><code class="kode">#{{ i.id }}</code></td>
              <td><b>{{ i.nama }}</b></td>
              <td>
                <span class="lokasi-wrap">
                  <MapPin class="icon-xs text-muted" /> {{ i.ruangan?.nama ?? '-' }}
                </span>
              </td>
              <td>
                <span class="kategori-wrap">
                  <Tag class="icon-xs text-muted" /> {{ i.kategori ?? '-' }}
                </span>
              </td>
              <td>
                <span class="jumlah-wrap">
                  <Layers class="icon-xs text-muted" /> {{ i.jumlah }}
                </span>
              </td>
              <td>
                <span :class="badgeKondisi(i.kondisi)" class="badge">
                  <CircleCheck v-if="i.kondisi === 'Baik'"         class="badge-icon" />
                  <CircleMinus v-else-if="i.kondisi === 'Rusak Ringan'" class="badge-icon" />
                  <CircleX     v-else                               class="badge-icon" />
                  {{ i.kondisi }}
                </span>
              </td>
              <td>
                <span :class="badgeStatus(i.status)" class="badge">
                  <CircleCheck v-if="i.status?.toLowerCase().includes('tersedia')" class="badge-icon" />
                  <Clock       v-else-if="i.status?.toLowerCase().includes('dipinjam')" class="badge-icon" />
                  <CircleX     v-else                                               class="badge-icon" />
                  {{ i.status }}
                </span>
              </td>
              <td v-if="canEdit" class="aksi-col">
                <button class="btn btn-sm btn-outline" @click="openEdit(i)">
                  <Pencil class="icon-btn" />
                </button>
                <button class="btn btn-sm btn-danger" @click="hapus(i.id)">
                  <Trash2 class="icon-btn" />
                </button>
              </td>
            </tr>
            <tr v-if="!filtered.length">
              <td :colspan="canEdit ? 8 : 7" class="empty-cell">
                <PackageX class="empty-icon" />
                <span>Belum ada data aset.</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Tambah / Edit -->
    <div v-if="modal" class="modal-mask" @click.self="modal = null">
      <div class="modal">
        <div class="modal-header">
          <component :is="modal.id ? Pencil : PackagePlus" class="modal-header-icon" />
          <h3>{{ modal.id ? 'Edit Aset' : 'Tambah Aset Baru' }}</h3>
          <button class="btn-close" @click="modal = null"><X :size="18" /></button>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label><Package class="icon-xs" /> Nama Aset</label>
            <div class="input-wrap">
              <Boxes class="input-icon" />
              <input v-model="modal.nama" placeholder="Komputer Workstation" class="has-icon" />
            </div>
          </div>
          <div class="form-group">
            <label><Tag class="icon-xs" /> Kategori</label>
            <div class="input-wrap">
              <Tag class="input-icon" />
              <input v-model="modal.kategori" placeholder="Komputer" class="has-icon" />
            </div>
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label><Layers class="icon-xs" /> Jumlah</label>
            <div class="input-wrap">
              <Hash class="input-icon" />
              <input v-model.number="modal.jumlah" type="number" min="1" class="has-icon" />
            </div>
          </div>
          <div class="form-group">
            <label><DoorOpen class="icon-xs" /> Ruangan / Lab</label>
            <div class="input-wrap">
              <select v-model="modal.ruangan_id">
                <option value="">-- Pilih Ruangan --</option>
                <option v-for="r in data.ruangan" :key="r.id" :value="r.id">{{ r.nama }}</option>
              </select>
              <ChevronDown class="select-arrow" />
            </div>
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label><ShieldCheck class="icon-xs" /> Kondisi</label>
            <div class="input-wrap">
              <select v-model="modal.kondisi">
                <option>Baik</option>
                <option>Rusak Ringan</option>
                <option>Rusak</option>
              </select>
              <ChevronDown class="select-arrow" />
            </div>
          </div>
          <div class="form-group">
            <label><Activity class="icon-xs" /> Status</label>
            <div class="input-wrap">
              <select v-model="modal.status">
                <option>Tersedia</option>
                <option>Dipinjam</option>
                <option>Tidak Tersedia</option>
              </select>
              <ChevronDown class="select-arrow" />
            </div>
          </div>
        </div>

        <div class="form-group">
          <label><FileText class="icon-xs" /> Keterangan</label>
          <textarea v-model="modal.keterangan" rows="2" placeholder="Keterangan tambahan (opsional)"></textarea>
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
  Package, PackagePlus, PackageX, Boxes,
  Hash, Tag, Layers, DoorOpen, MapPin,
  ShieldCheck, Activity, Settings2, Search,
  PlusCircle, Pencil, Trash2, ChevronDown,
  CircleCheck, CircleMinus, CircleX, Clock,
  FileText, Save, Loader2, X
} from 'lucide-vue-next'

const data    = useDataStore()
const auth    = useAuthStore()
const search  = ref('')
const modal   = ref(null)
const loading = ref(false)

const canEdit = computed(() => ['aslab', 'admin'].includes(auth.role))

const filtered = computed(() => {
  const q = search.value.toLowerCase()
  return data.inventaris.filter(i =>
    !q ||
    i.nama?.toLowerCase().includes(q) ||
    i.kategori?.toLowerCase().includes(q) ||
    i.ruangan?.nama?.toLowerCase().includes(q)
  )
})

function openTambah() {
  modal.value = { nama: '', kategori: '', jumlah: 1, ruangan_id: '', kondisi: 'Baik', status: 'Tersedia', keterangan: '' }
}
function openEdit(i) {
  modal.value = { ...i, ruangan_id: i.ruangan_id ?? '' }
}

async function simpan() {
  loading.value = true
  try {
    if (modal.value.id) {
      await api.put(`/inventaris/${modal.value.id}`, modal.value)
    } else {
      await api.post('/inventaris', modal.value)
    }
    await data.fetchInventaris()
    modal.value = null
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menyimpan')
  } finally { loading.value = false }
}

async function hapus(id) {
  if (!confirm('Hapus aset ini?')) return
  await api.delete(`/inventaris/${id}`)
  await data.fetchInventaris()
}

function badgeKondisi(k) {
  if (k === 'Baik')         return 'badge-green'
  if (k === 'Rusak Ringan') return 'badge-amber'
  return 'badge-red'
}
function badgeStatus(s) {
  if (!s) return ''
  const lower = s.toLowerCase()
  if (lower.includes('tersedia')) return 'badge-green'
  if (lower.includes('dipinjam')) return 'badge-amber'
  return 'badge-red'
}

onMounted(async () => {
  await data.fetchInventaris().catch(() => {})
  await data.fetchRuangan?.().catch(() => {})
})
</script>

<style scoped>
/* Heading */
.page-heading { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 20px; }
.page-heading h1 { font-size: 22px; font-weight: 700; color: #0f1f3d; margin-bottom: 4px; }
.page-heading p  { font-size: 14px; color: #64748b; }
.heading-icon { width: 28px; height: 28px; color: #2563eb; margin-top: 2px; flex-shrink: 0; }

/* Filter bar */
.filter-bar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
.search-wrap { position: relative; flex: 1; max-width: 360px; }
.search-icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: #94a3b8; pointer-events: none; }
.search-input { width: 100%; padding: 10px 14px 10px 36px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box; }
.search-input:focus { border-color: #2563eb; }

/* Card & table */
.card { background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 16px rgba(15,31,61,.08); }
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th { text-align: left; padding: 8px 10px; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; white-space: nowrap; }
td { padding: 11px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.aksi-col { white-space: nowrap; display: flex; gap: 6px; }

/* Kode */
.kode { font-size: 11px; background: #f0f4f8; padding: 2px 7px; border-radius: 4px; color: #475569; }

/* Inline cell helpers */
.lokasi-wrap,
.kategori-wrap,
.jumlah-wrap { display: inline-flex; align-items: center; gap: 4px; color: #475569; }
.text-muted { color: #94a3b8; }

/* Badge */
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-icon { width: 11px; height: 11px; }
.badge-green { background: #d1fae5; color: #065f46; }
.badge-amber { background: #fef3c7; color: #92400e; }
.badge-red   { background: #fee2e2; color: #991b1b; }

/* Empty state */
.empty-cell { text-align: center; color: #94a3b8; padding: 36px !important; }
.empty-cell { display: flex; flex-direction: column; align-items: center; gap: 10px; }
.empty-icon { width: 34px; height: 34px; color: #cbd5e1; }

/* Input wrap */
.input-wrap { position: relative; }
.input-wrap input,
.input-wrap select {
  width: 100%; padding: 9px 12px; border: 2px solid #e2e8f0;
  border-radius: 8px; font-size: 14px; font-family: inherit; outline: none;
  appearance: none; -webkit-appearance: none; background: #fff;
  box-sizing: border-box;
}
.input-wrap input.has-icon { padding-left: 36px; }
.input-wrap input:focus,
.input-wrap select:focus { border-color: #2563eb; }
.input-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: #94a3b8; pointer-events: none; }
.select-arrow { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: #94a3b8; pointer-events: none; }
.form-group textarea { width: 100%; padding: 9px 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; font-family: inherit; outline: none; resize: vertical; box-sizing: border-box; }
.form-group textarea:focus { border-color: #2563eb; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all .2s; }
.btn-primary { background: #0f1f3d; color: #fff; }
.btn-primary:hover:not(:disabled) { background: #1a3260; }
.btn-outline { background: transparent; border: 2px solid #e2e8f0; color: #0f1f3d; }
.btn-outline:hover { border-color: #0f1f3d; }
.btn-danger  { background: #fee2e2; color: #991b1b; border: none; }
.btn-danger:hover { background: #fecaca; }
.btn-sm { padding: 5px 9px; font-size: 12px; }
.btn:disabled { opacity: .6; cursor: not-allowed; }
.btn-close { margin-left: auto; background: none; border: none; cursor: pointer; color: #64748b; display: flex; align-items: center; padding: 4px; border-radius: 6px; transition: background .15s; }
.btn-close:hover { background: #f1f5f9; color: #0f1f3d; }

/* Modal */
.modal-mask { position: fixed; inset: 0; background: rgba(0,0,0,.4); display: flex; align-items: center; justify-content: center; z-index: 300; }
.modal { background: #fff; border-radius: 12px; padding: 28px; width: 520px; box-shadow: 0 8px 32px rgba(15,31,61,.2); }
.modal-header { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
.modal-header h3 { font-size: 18px; font-weight: 700; color: #0f1f3d; margin: 0; }
.modal-header-icon { width: 20px; height: 20px; color: #2563eb; flex-shrink: 0; }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-group { margin-bottom: 14px; }
.form-group label { display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: #0f1f3d; margin-bottom: 5px; }
.modal-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px; }

/* Icon sizes */
.icon-xs  { width: 13px; height: 13px; }
.icon-btn { width: 14px; height: 14px; }

/* Spinner */
.spinning { animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>