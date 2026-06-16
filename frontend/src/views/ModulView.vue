<template>
  <div>
    <div class="page-heading">
      <h1>Modul Praktikum</h1>
      <p>{{ canUpload ? 'Unggah dan kelola modul pembelajaran.' : 'Unduh modul praktikum Anda.' }}</p>
    </div>

    <!-- Form unggah (asprak/dosen) -->
    <div v-if="canUpload" class="card mb-4">
      <div class="card-title mb-4 flex items-center gap-2">
        <Upload :size="18" /> Unggah Modul Baru
      </div>
      <div class="form-row">
        <div class="form-group">
          <label><BookOpen :size="12" /> Judul Modul</label>
          <input type="text" v-model="form.judul" placeholder="Modul 4 - REST API..." />
        </div>
        <div class="form-group">
          <label><GraduationCap :size="12" /> Mata Kuliah</label>
          <select v-model="form.matkul">
            <option v-for="m in data.matkul" :key="m.id">{{ m.nama }}</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label><Paperclip :size="12" /> File Modul</label>
        <div class="file-input-wrapper">
          <label class="file-label" :class="{ 'has-file': selectedFile }">
            <FileUp :size="18" class="file-icon" />
            <span>{{ selectedFile ? selectedFile.name : 'Pilih file (.pdf, .doc, .docx)' }}</span>
            <input type="file" ref="fileInput" accept=".pdf,.doc,.docx" @change="onFileChange" class="file-hidden" />
          </label>
        </div>
      </div>
      <button class="btn btn-primary" @click="unggahModul">
        <Upload :size="15" /> Unggah Modul
      </button>
    </div>

    <!-- Daftar modul -->
    <div class="card">
      <div class="card-title mb-4">
        <FolderOpen :size="18" /> Daftar Modul Tersedia
      </div>
      <div style="display:flex;flex-direction:column;gap:10px">
        <div v-for="m in data.modul" :key="m.id" class="modul-item">
          <div class="modul-icon">
            <FileText :size="22" />
          </div>
          <div style="flex:1">
            <div style="font-size:14px;font-weight:700">{{ m.judul }}</div>
            <div class="modul-meta">
              <span><BookMarked :size="11" /> {{ m.matkul || m.mata_kuliah }}</span>
              <span><UserRound :size="11" /> {{ m.dosen }}</span>
              <span><CalendarDays :size="11" /> {{ m.tanggal }}</span>
              <span><HardDrive :size="11" /> {{ m.ukuran }}</span>
              <span class="badge-format">{{ m.format || 'PDF' }}</span>
            </div>
          </div>
          <div style="display:flex;gap:8px">
            <button class="btn btn-primary btn-sm" @click="unduh(m)">
              <Download :size="14" /> Unduh
            </button>
            <button v-if="canUpload" class="btn btn-danger btn-sm" @click="hapus(m.id)">
              <Trash2 :size="14" />
            </button>
          </div>
        </div>
        <div v-if="data.modul.length === 0" class="empty-state">
          <BookOpen :size="32" />
          <p>Belum ada modul tersedia.</p>
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
  Upload,
  FileText,
  FileUp,
  Download,
  Trash2,
  BookOpen,
  BookMarked,
  FolderOpen,
  GraduationCap,
  Paperclip,
  UserRound,
  CalendarDays,
  HardDrive,
} from 'lucide-vue-next'

const data = useDataStore(); const auth = useAuthStore(); const ui = useUiStore()
const canUpload = computed(() => ['asprak', 'dosen'].includes(auth.role))
const fileInput = ref(null)
const selectedFile = ref(null)
const form = reactive({ judul: '', matkul: '' })

onMounted(() => {
  data.fetchModul().catch(() => {})
  data.fetchMatkul?.().catch(() => {})
})

function onFileChange(e) {
  selectedFile.value = e.target.files[0] || null
}

async function unggahModul() {
  if (!form.judul) { ui.showError('Gagal', 'Lengkapi judul modul!'); return }
  if (!selectedFile.value) { ui.showError('Gagal', 'Pilih file modul terlebih dahulu!'); return }
  const f = selectedFile.value
  const ukuran = f.size
  const ext = f.name.split('.').pop().toUpperCase()
  const reader = new FileReader()
  reader.onload = async (ev) => {
    try {
      await api.post('/modul', {
        judul: form.judul, mata_kuliah: form.matkul, deskripsi: '',
        nama_file: f.name, mime_type: f.type || 'application/octet-stream',
        ukuran, format: ext, data_base64: ev.target.result,
      })
      ui.showSuccess('Berhasil', `Modul berhasil diunggah! (${ext})`)
      Object.assign(form, { judul: '', matkul: '' })
      selectedFile.value = null
      if (fileInput.value) fileInput.value.value = ''
      data.fetchModul()
    } catch (err) {
      ui.showError('Gagal', err.response?.data?.message || 'Gagal unggah modul')
    }
  }
  reader.readAsDataURL(f)
}

async function unduh(m) {
  try {
    const { data: d } = await api.get(`/modul/${m.id}/unduh`)
    const a = document.createElement('a')
    a.href = d.data_base64
    a.download = d.nama_file
    a.click()
  } catch {
    ui.showError('Gagal', 'Gagal mengunduh modul')
  }
}

async function hapus(id) {
  if (!confirm('Hapus modul?')) return
  try {
    await api.delete(`/modul/${id}`)
    await data.fetchModul()
    ui.showSuccess('Berhasil', 'Modul dihapus.')
  } catch {
    ui.showError('Gagal', 'Gagal menghapus modul.')
  }
}
</script>

<style scoped>
.page-heading { margin-bottom: 20px; }
.page-heading h1 { font-size: 22px; font-weight: 700; color: #0f1f3d; margin-bottom: 4px; }
.page-heading p  { font-size: 14px; color: #64748b; }

.card-title { display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 700; color: #0f1f3d; }

/* Modul item */
.modul-item {
  display: flex; align-items: center; gap: 16px;
  padding: 14px; background: var(--bg, #f8fafc);
  border-radius: var(--radius, 10px);
}
.modul-icon {
  width: 44px; height: 44px;
  background: #fef3c7; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  color: #b45309; flex-shrink: 0;
}
.modul-meta {
  display: flex; flex-wrap: wrap; align-items: center; gap: 10px;
  font-size: 12px; color: #64748b; margin-top: 4px;
}
.modul-meta span { display: flex; align-items: center; gap: 3px; }
.badge-format {
  background: #e0e7ff; color: #3730a3;
  font-size: 10px; font-weight: 700;
  padding: 1px 6px; border-radius: 4px;
  letter-spacing: .4px;
}

/* Custom file input */
.file-input-wrapper { margin-top: 4px; }
.file-label {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 14px;
  border: 2px dashed #cbd5e1; border-radius: 10px;
  cursor: pointer; font-size: 13px; color: #64748b;
  transition: border-color .2s, background .2s;
}
.file-label:hover { border-color: #2563eb; background: #f0f4ff; }
.file-label.has-file { border-color: #10b981; background: #f0fdf4; color: #065f46; }
.file-label .file-icon { flex-shrink: 0; color: #94a3b8; }
.file-label.has-file .file-icon { color: #10b981; }
.file-hidden { display: none; }

/* Form */
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-group { margin-bottom: 14px; }
.form-group label { display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: #0f1f3d; margin-bottom: 6px; }
.form-group input[type="text"],
.form-group select {
  width: 100%; padding: 9px 12px;
  border: 2px solid #e2e8f0; border-radius: 8px;
  font-size: 14px; font-family: inherit; outline: none;
  box-sizing: border-box;
}
.form-group input:focus,
.form-group select:focus { border-color: #2563eb; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all .2s; }
.btn:disabled { opacity: .6; cursor: not-allowed; }
.btn-primary { background: #0f1f3d; color: #fff; }
.btn-primary:hover:not(:disabled) { background: #1a3260; }
.btn-danger  { background: #fee2e2; color: #991b1b; }
.btn-danger:hover { background: #fecaca; }
.btn-sm { padding: 6px 11px; font-size: 12px; }

.empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px; color: #94a3b8; gap: 10px; }
.empty-state p { font-size: 14px; }

.mb-4 { margin-bottom: 16px; }
</style>