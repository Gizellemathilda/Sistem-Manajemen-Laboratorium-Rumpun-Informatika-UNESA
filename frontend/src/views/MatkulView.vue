<template>
  <div>
    <div class="page-heading">
      <h1>Mata Kuliah</h1>
      <p>{{ canEdit ? 'Kelola daftar mata kuliah praktikum.' : 'Daftar mata kuliah praktikum.' }}</p>
    </div>

    <div v-if="canEdit" style="margin-bottom:16px">
      <button class="btn btn-primary" @click="openTambah">
        <Plus :size="15" /> Tambah Mata Kuliah
      </button>
    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Kode</th>
              <th>Nama Mata Kuliah</th>
              <th>SKS</th>
              <th>Dosen Pengampu</th>
              <th v-if="canEdit">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="m in data.matkul" :key="m.id">
              <td><code class="kode">{{ m.kode }}</code></td>
              <td><b>{{ m.nama }}</b></td>
              <td>{{ m.sks }}</td>
              <td>{{ m.dosen }}</td>
              <td v-if="canEdit" class="aksi-col">
                <button class="btn btn-sm btn-outline" @click="openEdit(m)">
                  <Pencil :size="13" />
                </button>
                <button class="btn btn-sm btn-danger" @click="hapus(m.id)" style="margin-left:4px">
                  <Trash2 :size="13" />
                </button>
              </td>
            </tr>
            <tr v-if="!data.matkul.length">
              <td :colspan="canEdit ? 5 : 4" class="empty-cell">
                <BookOpen :size="28" style="margin-bottom:8px;opacity:.3" /><br>
                Belum ada mata kuliah.
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
          <h3>
            <BookMarked :size="18" />
            {{ modal.id ? 'Edit Mata Kuliah' : 'Tambah Mata Kuliah' }}
          </h3>
          <button class="close-btn" @click="modal = null"><X :size="18" /></button>
        </div>
        <div class="form-row-2">
          <div class="form-group">
            <label><Hash :size="12" /> Kode</label>
            <input v-model="modal.kode" placeholder="TI304" />
          </div>
          <div class="form-group">
            <label><Award :size="12" /> SKS</label>
            <input v-model.number="modal.sks" type="number" min="1" max="6" />
          </div>
        </div>
        <div class="form-group">
          <label><BookOpen :size="12" /> Nama Mata Kuliah</label>
          <input v-model="modal.nama" placeholder="Basis Data" />
        </div>
        <div class="form-group">
          <label><UserRound :size="12" /> Dosen Pengampu</label>
          <input v-model="modal.dosen" placeholder="Nama dosen" />
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
  Plus,
  Pencil,
  Trash2,
  BookOpen,
  BookMarked,
  BookMarked as BookMarkedIcon,
  Hash,
  Award,
  UserRound,
  Save,
  Loader2,
  X,
} from 'lucide-vue-next'

const data    = useDataStore()
const auth    = useAuthStore()
const modal   = ref(null)
const loading = ref(false)

const canEdit = computed(() => auth.role === 'admin')

function openTambah() { modal.value = { kode: '', nama: '', sks: 3, dosen: '' } }
function openEdit(m)  { modal.value = { ...m } }

async function simpan() {
  loading.value = true
  try {
    if (modal.value.id) {
      await api.put(`/matkul/${modal.value.id}`, modal.value)
    } else {
      await api.post('/matkul', modal.value)
    }
    await data.fetchMatkul()
    modal.value = null
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menyimpan')
  } finally { loading.value = false }
}

async function hapus(id) {
  if (!confirm('Hapus mata kuliah ini?')) return
  await api.delete(`/matkul/${id}`)
  await data.fetchMatkul()
}

onMounted(() => data.fetchMatkul?.().catch(() => {}))
</script>

<style scoped>
.page-heading { margin-bottom: 20px; }
.page-heading h1 { font-size: 22px; font-weight: 700; color: #0f1f3d; margin-bottom: 4px; }
.page-heading p  { font-size: 14px; color: #64748b; }

.card { background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 16px rgba(15,31,61,.08); }
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th { text-align: left; padding: 8px 10px; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; white-space: nowrap; }
td { padding: 11px 10px; border-bottom: 1px solid #f1f5f9; }
.empty-cell { text-align: center; color: #94a3b8; padding: 36px; line-height: 1.6; }
.aksi-col { white-space: nowrap; }
.kode { font-size: 11px; background: #f0f4f8; padding: 2px 7px; border-radius: 4px; font-family: monospace; }

.btn { display: inline-flex; align-items: center; gap: 5px; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all .2s; }
.btn:disabled { opacity: .6; cursor: not-allowed; }
.btn-primary { background: #0f1f3d; color: #fff; }
.btn-primary:hover:not(:disabled) { background: #1a3260; }
.btn-outline { background: transparent; border: 2px solid #e2e8f0; color: #0f1f3d; }
.btn-outline:hover { border-color: #0f1f3d; }
.btn-danger  { background: #fee2e2; color: #991b1b; border: none; }
.btn-danger:hover { background: #fecaca; }
.btn-sm { padding: 5px 10px; font-size: 12px; }

.modal-mask { position: fixed; inset: 0; background: rgba(0,0,0,.4); display: flex; align-items: center; justify-content: center; z-index: 300; }
.modal { background: #fff; border-radius: 12px; padding: 28px; width: 460px; box-shadow: 0 8px 32px rgba(15,31,61,.2); }
.modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.modal-header h3 { display: flex; align-items: center; gap: 8px; font-size: 18px; font-weight: 700; color: #0f1f3d; margin: 0; }
.close-btn { background: none; border: none; cursor: pointer; color: #94a3b8; display: flex; padding: 2px; border-radius: 6px; transition: color .2s, background .2s; }
.close-btn:hover { color: #0f1f3d; background: #f1f5f9; }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-group { margin-bottom: 14px; }
.form-group label { display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: #0f1f3d; margin-bottom: 5px; }
.form-group input { width: 100%; padding: 9px 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; font-family: inherit; outline: none; box-sizing: border-box; }
.form-group input:focus { border-color: #2563eb; }
.modal-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px; }

@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin .8s linear infinite; }
</style>