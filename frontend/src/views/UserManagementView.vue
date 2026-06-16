<template>
  <div>
    <div class="page-heading">
      <h1>Manajemen Pengguna</h1>
      <p>Kelola akun, hak akses, dan status semua pengguna.</p>
    </div>
    <div style="margin-bottom:16px">
      <button class="btn btn-primary" @click="openForm()">
        <UserPlus :size="15" /> Tambah Pengguna
      </button>
    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Nama</th><th>Email</th><th>NIM/NIP</th><th>Peran</th><th>Status</th><th>Aksi</th></tr>
          </thead>
          <tbody>
            <tr v-for="u in data.users" :key="u.id">
              <td><b>{{ u.nama }}</b></td>
              <td>
                <span class="td-email"><Mail :size="12" /> {{ u.email }}</span>
              </td>
              <td><code class="nim-code">{{ u.nim_nip || u.noid || '-' }}</code></td>
              <td>
                <span class="badge" :class="roleBadge(u.role)">
                  <component :is="roleIcon(u.role)" :size="11" />
                  {{ roleLabel(u.role) }}
                </span>
              </td>
              <td>
                <span class="badge" :class="u.status === 'Aktif' ? 'badge-green' : 'badge-red'">
                  <CheckCircle2 v-if="u.status === 'Aktif'" :size="11" />
                  <XCircle v-else :size="11" />
                  {{ u.status }}
                </span>
              </td>
              <td style="white-space:nowrap">
                <button class="btn btn-sm btn-outline" @click="openForm(u)">
                  <Pencil :size="13" /> Edit
                </button>
                <button
                  class="btn btn-sm"
                  :class="u.status === 'Aktif' ? 'btn-warning' : 'btn-success'"
                  style="margin-left:4px"
                  @click="toggleStatus(u.id)"
                >
                  <UserX v-if="u.status === 'Aktif'" :size="13" />
                  <UserCheck v-else :size="13" />
                  {{ u.status === 'Aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
                <button class="btn btn-sm btn-danger" style="margin-left:4px" @click="hapus(u.id)">
                  <Trash2 :size="13" />
                </button>
              </td>
            </tr>
            <tr v-if="data.users.length === 0">
              <td colspan="6" class="empty-cell">
                <Users :size="26" style="margin-bottom:6px;opacity:.3" /><br>
                Belum ada pengguna
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal form -->
    <div v-if="showForm" class="modal-overlay" @click.self="showForm=false">
      <div class="modal">
        <div class="modal-header">
          <h3>
            <UserCog :size="18" />
            {{ form.id ? 'Edit' : 'Tambah' }} Pengguna
          </h3>
          <button class="modal-close" @click="showForm=false">
            <X :size="18" />
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label><UserRound :size="12" /> Nama Lengkap</label>
            <input type="text" v-model="form.nama" placeholder="Nama lengkap" />
          </div>
          <div class="form-row">
            <div class="form-group">
              <label><Mail :size="12" /> Email</label>
              <input type="email" v-model="form.email" placeholder="user@unesa.ac.id" />
            </div>
            <div class="form-group">
              <label><Hash :size="12" /> NIM / NIP</label>
              <input type="text" v-model="form.nim_nip" placeholder="240512xxx atau 19xxx" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label><ShieldCheck :size="12" /> Peran</label>
              <select v-model="form.role">
                <option value="mahasiswa">Mahasiswa</option>
                <option value="asprak">Asisten Praktikum</option>
                <option value="aslab">Asisten Lab</option>
                <option value="dosen">Dosen</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div class="form-group">
              <label><ToggleRight :size="12" /> Status</label>
              <select v-model="form.status">
                <option>Aktif</option>
                <option>Nonaktif</option>
              </select>
            </div>
          </div>
          <div v-if="!form.id" class="form-group">
            <label><KeyRound :size="12" /> Password Awal</label>
            <input type="text" v-model="form.password" placeholder="password123" />
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
import { reactive, ref, onMounted } from 'vue'
import api from '@/api'
import { useDataStore } from '@/stores/data'
import { useUiStore } from '@/stores/ui'
import {
  UserPlus,
  UserCog,
  UserRound,
  UserCheck,
  UserX,
  Users,
  Mail,
  Hash,
  ShieldCheck,
  ToggleRight,
  KeyRound,
  Pencil,
  Trash2,
  CheckCircle2,
  XCircle,
  Save,
  X,
  GraduationCap,
  BookOpen,
  Wrench,
  Settings,
  FlaskConical,
} from 'lucide-vue-next'

const data = useDataStore(); const ui = useUiStore()
const showForm = ref(false)
const form = reactive({ id: null, nama: '', email: '', role: 'mahasiswa', nim_nip: '', status: 'Aktif', password: '' })

onMounted(() => data.fetchUsers().catch(() => {}))

const roleLabelMap = {
  mahasiswa: 'Mahasiswa',
  asprak:    'Asisten Praktikum',
  aslab:     'Asisten Lab',
  dosen:     'Dosen',
  admin:     'Admin',
}
function roleLabel(r) { return roleLabelMap[r] || r }
function roleBadge(r) {
  return r === 'mahasiswa' ? 'badge-blue'
       : r === 'dosen'     ? 'badge-green'
       : r === 'admin'     ? 'badge-amber'
       : 'badge-gray'
}
function roleIcon(r) {
  return r === 'mahasiswa' ? GraduationCap
       : r === 'dosen'     ? BookOpen
       : r === 'asprak'    ? FlaskConical
       : r === 'aslab'     ? Wrench
       : Settings
}

function openForm(u) {
  Object.assign(form, u
    ? { ...u, password: '' }
    : { id: null, nama: '', email: '', role: 'mahasiswa', nim_nip: '', status: 'Aktif', password: '' }
  )
  showForm.value = true
}

async function simpan() {
  if (!form.nama || !form.email) { ui.showError('Gagal', 'Lengkapi nama dan email!'); return }
  try {
    if (form.id) await api.put(`/users/${form.id}`, form)
    else         await api.post('/users', form)
    showForm.value = false
    await data.fetchUsers()
    ui.showSuccess('Berhasil', 'Pengguna disimpan.')
  } catch (e) { ui.showError('Gagal', e.response?.data?.message || 'Gagal') }
}

async function toggleStatus(id) {
  try {
    await api.post(`/users/${id}/toggle`)
    await data.fetchUsers()
  } catch { ui.showError('Gagal', 'Gagal mengubah status.') }
}

async function hapus(id) {
  if (!confirm('Hapus pengguna ini?')) return
  try {
    await api.delete(`/users/${id}`)
    await data.fetchUsers()
    ui.showSuccess('Berhasil', 'Pengguna dihapus.')
  } catch { ui.showError('Gagal', 'Gagal menghapus pengguna.') }
}
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

.td-email { display: inline-flex; align-items: center; gap: 5px; color: #475569; }
.nim-code { font-size: 11px; background: #f0f4f8; padding: 2px 7px; border-radius: 4px; font-family: monospace; }
.empty-cell { text-align: center; color: #94a3b8; padding: 32px; line-height: 1.8; }

/* Badge */
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-green { background: #d1fae5; color: #065f46; }
.badge-amber { background: #fef3c7; color: #92400e; }
.badge-red   { background: #fee2e2; color: #991b1b; }
.badge-blue  { background: #dbeafe; color: #1e40af; }
.badge-gray  { background: #f1f5f9; color: #475569; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all .2s; }
.btn:disabled { opacity: .6; cursor: not-allowed; }
.btn-primary { background: #0f1f3d; color: #fff; }
.btn-primary:hover { background: #1a3260; }
.btn-success { background: #d1fae5; color: #065f46; }
.btn-success:hover { background: #a7f3d0; }
.btn-warning { background: #fef3c7; color: #92400e; }
.btn-warning:hover { background: #fde68a; }
.btn-danger  { background: #fee2e2; color: #991b1b; }
.btn-danger:hover  { background: #fecaca; }
.btn-outline { background: transparent; border: 2px solid #e2e8f0; color: #0f1f3d; }
.btn-outline:hover { border-color: #0f1f3d; }
.btn-sm { padding: 6px 12px; font-size: 12px; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { background: #fff; border-radius: 12px; padding: 24px; width: 100%; max-width: 520px; box-shadow: 0 8px 32px rgba(0,0,0,.2); }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.modal-header h3 { display: flex; align-items: center; gap: 8px; font-size: 16px; font-weight: 700; color: #0f1f3d; margin: 0; }
.modal-close { background: none; border: none; cursor: pointer; color: #94a3b8; display: flex; padding: 4px; border-radius: 6px; transition: color .2s, background .2s; }
.modal-close:hover { color: #0f1f3d; background: #f1f5f9; }
.modal-body { margin-bottom: 20px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 8px; }

/* Form */
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-group { margin-bottom: 14px; }
.form-group label { display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: #0f1f3d; margin-bottom: 5px; }
.form-group input,
.form-group select { width: 100%; padding: 9px 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; font-family: inherit; outline: none; box-sizing: border-box; }
.form-group input:focus,
.form-group select:focus { border-color: #2563eb; }
</style>