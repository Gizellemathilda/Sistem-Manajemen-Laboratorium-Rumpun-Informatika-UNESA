<template>
  <div>
    <div class="page-heading"><h1>Nilai Praktikum</h1></div>

    <div v-if="canEdit" style="margin-bottom:16px">
      <button class="btn btn-primary" @click="openForm()">
        <PlusCircle :size="15" /> Input Nilai Mahasiswa
      </button>
    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Mahasiswa</th><th>Mata Kuliah</th>
              <th>Tugas</th><th>UTS</th><th>UAS</th>
              <th>Rata-rata</th><th>Grade</th>
              <th v-if="canEdit">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="n in data.nilai" :key="n.id">
              <td><b>{{ n.mahasiswa }}</b></td>
              <td>{{ n.matkul }}</td>
              <td>{{ n.tugas }}</td>
              <td>{{ n.uts }}</td>
              <td>{{ n.uas }}</td>
              <td><b>{{ rataRata(n) }}</b></td>
              <td>
                <span class="badge" :class="gradeBadgeClass(hitungGrade(rataRata(n)))">
                  {{ hitungGrade(rataRata(n)) }}
                </span>
              </td>
              <td v-if="canEdit">
                <button class="btn btn-sm btn-outline" @click="openForm(n)">
                  <Pencil :size="13" />
                </button>
              </td>
            </tr>
            <tr v-if="data.nilai.length === 0">
              <td colspan="8" class="empty-cell">
                <ClipboardX :size="28" style="margin-bottom:8px;opacity:.3" /><br>
                Belum ada data nilai
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal form -->
    <div v-if="showForm" class="modal-mask" @click.self="showForm=false">
      <div class="modal">
        <div class="modal-header">
          <h3>
            <ClipboardEdit :size="18" />
            {{ form.id ? 'Edit Nilai' : 'Input Nilai Mahasiswa' }}
          </h3>
          <button class="modal-close" @click="showForm=false">
            <X :size="18" />
          </button>
        </div>
        <div class="modal-body">
          <template v-if="!form.id">
            <div class="form-group">
              <label><UserRound :size="12" /> Mahasiswa</label>
              <input type="text" v-model="form.mahasiswa" placeholder="Nama mahasiswa" />
            </div>
            <div class="form-group">
              <label><BookOpen :size="12" /> Mata Kuliah</label>
              <select v-model="form.matkul">
                <option value="">-- Pilih Mata Kuliah --</option>
                <option v-for="m in data.matkul" :key="m.id" :value="m.nama">{{ m.nama }}</option>
              </select>
            </div>
          </template>
          <template v-else>
            <div class="form-group">
              <label><UserRound :size="12" /> Mahasiswa</label>
              <input type="text" :value="form.mahasiswa" disabled style="background:#f0f4f8" />
            </div>
          </template>
          <div class="form-row">
            <div class="form-group">
              <label><NotebookPen :size="12" /> Tugas</label>
              <input type="number" v-model.number="form.tugas" min="0" max="100" />
            </div>
            <div class="form-group">
              <label><FileCheck :size="12" /> UTS</label>
              <input type="number" v-model.number="form.uts" min="0" max="100" />
            </div>
          </div>
          <div class="form-group">
            <label><FileCheck2 :size="12" /> UAS</label>
            <input type="number" v-model.number="form.uas" min="0" max="100" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showForm=false">
            <X :size="14" /> Batal
          </button>
          <button class="btn btn-primary" @click="simpan">
            <Save :size="14" /> Simpan
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted, computed } from 'vue'
import api from '@/api'
import { useDataStore } from '@/stores/data'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'
import {
  PlusCircle,
  Pencil,
  ClipboardX,
  ClipboardEdit,
  UserRound,
  BookOpen,
  NotebookPen,
  FileCheck,
  FileCheck2,
  Save,
  X,
} from 'lucide-vue-next'

const data = useDataStore(); const auth = useAuthStore(); const ui = useUiStore()
const showForm = ref(false)
const form = reactive({ id: null, mahasiswa: '', matkul: '', tugas: 0, uts: 0, uas: 0 })
const canEdit = computed(() => ['dosen', 'asprak'].includes(auth.role))

onMounted(() => {
  data.fetchNilai().catch(() => {})
  data.fetchMatkul?.().catch(() => {})
})

function rataRata(n) {
  return ((Number(n.tugas) + Number(n.uts) + Number(n.uas)) / 3).toFixed(1)
}

function hitungGrade(avg) {
  const v = parseFloat(avg)
  return v >= 85 ? 'A' : v >= 80 ? 'A-' : v >= 75 ? 'B+' : v >= 70 ? 'B' : v >= 65 ? 'B-' : 'C+'
}

function gradeBadgeClass(grade) {
  if (grade === 'A')  return 'badge-a'
  if (grade === 'A-') return 'badge-a-minus'
  if (grade === 'B+') return 'badge-b-plus'
  if (grade === 'B')  return 'badge-b'
  return 'badge-c'
}

function openForm(n) {
  Object.assign(form, n
    ? { ...n }
    : { id: null, mahasiswa: '', matkul: '', tugas: 0, uts: 0, uas: 0 }
  )
  showForm.value = true
}

async function simpan() {
  try {
    if (form.id) await api.put(`/nilai/${form.id}`, { tugas: form.tugas, uts: form.uts, uas: form.uas })
    else         await api.post('/nilai', { mahasiswa: form.mahasiswa, matkul: form.matkul, tugas: form.tugas, uts: form.uts, uas: form.uas })
    showForm.value = false
    await data.fetchNilai()
    ui.showSuccess('Berhasil', 'Nilai berhasil disimpan.')
  } catch {
    ui.showError('Gagal', 'Gagal menyimpan nilai.')
  }
}
</script>

<style scoped>
.page-heading { margin-bottom: 20px; }
.page-heading h1 { font-size: 22px; font-weight: 700; color: #0f1f3d; }

.card { background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 16px rgba(15,31,61,.08); }
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th { text-align: left; padding: 8px 10px; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
td { padding: 11px 10px; border-bottom: 1px solid #f1f5f9; }

.empty-cell { text-align: center; color: #94a3b8; padding: 36px; line-height: 1.8; }

/* Grade badges */
.badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.badge-a       { background: #d1fae5; color: #065f46; }
.badge-a-minus { background: #dcfce7; color: #166534; }
.badge-b-plus  { background: #dbeafe; color: #1e40af; }
.badge-b       { background: #e0e7ff; color: #3730a3; }
.badge-c       { background: #fef9c3; color: #854d0e; }

.btn { display: inline-flex; align-items: center; gap: 5px; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all .2s; }
.btn-primary { background: #0f1f3d; color: #fff; }
.btn-primary:hover { background: #1a3260; }
.btn-outline { background: transparent; border: 2px solid #e2e8f0; color: #0f1f3d; }
.btn-outline:hover { border-color: #0f1f3d; }
.btn-sm { padding: 5px 10px; font-size: 12px; }

.modal-mask { position: fixed; inset: 0; background: rgba(0,0,0,.4); display: flex; align-items: center; justify-content: center; z-index: 300; }
.modal { background: #fff; border-radius: 12px; padding: 28px; width: 480px; box-shadow: 0 8px 32px rgba(15,31,61,.2); }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.modal-header h3 { display: flex; align-items: center; gap: 8px; font-size: 18px; font-weight: 700; color: #0f1f3d; }
.modal-close { background: none; border: none; cursor: pointer; color: #94a3b8; display: flex; padding: 4px; border-radius: 6px; transition: color .2s, background .2s; }
.modal-close:hover { color: #0f1f3d; background: #f1f5f9; }
.modal-body { margin-bottom: 16px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 8px; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-group { margin-bottom: 14px; }
.form-group label { display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: #0f1f3d; margin-bottom: 5px; }
.form-group input,
.form-group select { width: 100%; padding: 9px 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; font-family: inherit; outline: none; box-sizing: border-box; }
.form-group input:focus,
.form-group select:focus { border-color: #2563eb; }
</style>