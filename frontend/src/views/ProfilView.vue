<template>
  <div>
    <div class="page-heading">
      <h1>Profil Saya</h1>
      <p>Kelola informasi akun &amp; foto profil Anda.</p>
    </div>

    <div class="grid-2">

      <!-- ── Kartu Avatar (kiri) ── -->
      <div class="card card-center">
        <div class="avatar-wrap">
          <img v-if="form.foto" :src="form.foto" class="avatar-img" alt="foto profil" />
          <div v-else class="avatar-letter">{{ firstChar }}</div>
        </div>

        <div class="pf-name">{{ form.nama || auth.user?.nama }}</div>
        <div class="pf-role">
          <ShieldCheck :size="13" style="display:inline;vertical-align:middle;margin-right:3px" />
          {{ roleLabel }}
        </div>
        <div v-if="auth.user?.nim_nip" class="pf-sub">
          <Hash :size="12" style="display:inline;vertical-align:middle;margin-right:2px" />
          {{ isNIP ? 'NIP' : 'NIM' }}: {{ auth.user.nim_nip }}
        </div>
        <div class="pf-email">
          <Mail :size="12" style="display:inline;vertical-align:middle;margin-right:3px" />
          {{ auth.user?.email }}
        </div>

        <div class="avatar-actions">
          <label class="btn btn-outline btn-sm" style="cursor:pointer">
            <Camera :size="14" /> Ganti Foto Profil
            <input type="file" accept="image/*" style="display:none" @change="onFile" />
          </label>
          <button v-if="form.foto" class="btn btn-danger btn-sm" @click="hapusFoto">
            <Trash2 :size="14" /> Hapus Foto
          </button>
        </div>
      </div>

      <!-- ── Form Edit (kanan) ── -->
      <div class="card">
        <div class="card-title">
          <UserCog :size="17" /> Edit Profil
        </div>

        <div class="form-group">
          <label><UserRound :size="12" /> Nama Lengkap</label>
          <input v-model="form.nama" @input="liveUpdateNama" placeholder="Nama lengkap" />
        </div>

        <div class="form-group">
          <label><Mail :size="12" /> Email</label>
          <input :value="auth.user?.email" disabled class="input-disabled" />
          <div class="form-hint"><Info :size="11" /> Email tidak dapat diubah.</div>
        </div>

        <div class="form-group">
          <label><Hash :size="12" /> NIM / NIP</label>
          <input v-model="form.nim_nip" placeholder="NIM atau NIP" />
        </div>

        <div class="form-group">
          <label><Phone :size="12" /> No. HP</label>
          <input v-model="form.no_hp" placeholder="08xxxxxxxxxx" />
        </div>

        <div class="divider"></div>
        <div class="section-label">
          <KeyRound :size="13" style="display:inline;vertical-align:middle;margin-right:5px" />
          Ubah Password
        </div>

        <div class="form-group">
          <label><Lock :size="12" /> Password Baru</label>
          <div class="input-wrap">
            <input
              v-model="form.password"
              :type="showPwd ? 'text' : 'password'"
              placeholder="Kosongkan jika tidak ingin diubah"
            />
            <button type="button" class="eye-btn" @click="showPwd = !showPwd" tabindex="-1">
              <Eye v-if="!showPwd" :size="15" />
              <EyeOff v-else :size="15" />
            </button>
          </div>
        </div>

        <div class="form-group">
          <label><LockKeyhole :size="12" /> Konfirmasi Password</label>
          <div class="input-wrap">
            <input
              v-model="form.password_confirmation"
              :type="showPwdC ? 'text' : 'password'"
              placeholder="Ulangi password baru"
            />
            <button type="button" class="eye-btn" @click="showPwdC = !showPwdC" tabindex="-1">
              <Eye v-if="!showPwdC" :size="15" />
              <EyeOff v-else :size="15" />
            </button>
          </div>
          <div v-if="pwdMismatch" class="form-error">
            <AlertCircle :size="11" /> Password dan konfirmasi tidak sama.
          </div>
          <div v-if="pwdTooShort" class="form-error">
            <AlertCircle :size="11" /> Password minimal 6 karakter.
          </div>
        </div>

        <button class="btn btn-primary" @click="simpan" :disabled="loading || pwdMismatch || pwdTooShort">
          <Loader2 v-if="loading" :size="15" class="spin" />
          <Save v-else :size="15" />
          {{ loading ? 'Menyimpan…' : 'Simpan Perubahan' }}
        </button>

        <div v-if="successMsg" class="alert-success">
          <CheckCircle2 :size="14" /> {{ successMsg }}
        </div>
        <div v-if="errorMsg" class="alert-error">
          <AlertCircle :size="14" /> {{ errorMsg }}
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import api from '@/api'
import { useAuthStore } from '@/stores/auth'
import {
  Camera,
  Trash2,
  UserCog,
  UserRound,
  Mail,
  Hash,
  Phone,
  Lock,
  LockKeyhole,
  KeyRound,
  Eye,
  EyeOff,
  Save,
  Loader2,
  Info,
  AlertCircle,
  CheckCircle2,
  ShieldCheck,
} from 'lucide-vue-next'

const auth = useAuthStore()

const loading    = ref(false)
const successMsg = ref('')
const errorMsg   = ref('')
const showPwd    = ref(false)
const showPwdC   = ref(false)

const form = reactive({
  nama: '',
  nim_nip: '',
  no_hp: '',
  foto: '',
  password: '',
  password_confirmation: '',
})

const ROLE_LABEL = {
  mahasiswa: 'Mahasiswa',
  asprak:    'Asisten Praktikum',
  aslab:     'Asisten Lab',
  dosen:     'Dosen',
  admin:     'Administrator',
}

const roleLabel  = computed(() => ROLE_LABEL[auth.user?.role] || auth.user?.role || '')
const firstChar  = computed(() => (form.nama || auth.user?.nama || 'U').charAt(0).toUpperCase())
const isNIP      = computed(() => ['dosen', 'admin', 'aslab'].includes(auth.user?.role))

const pwdMismatch = computed(() =>
  !!form.password && !!form.password_confirmation && form.password !== form.password_confirmation
)
const pwdTooShort = computed(() =>
  !!form.password && form.password.length < 6
)

onMounted(() => {
  if (auth.user) {
    form.nama    = auth.user.nama    ?? ''
    form.nim_nip = auth.user.nim_nip ?? ''
    form.no_hp   = auth.user.no_hp   ?? ''
    form.foto    = auth.user.foto    ?? ''
  }
})

function liveUpdateNama() {
  auth.updateUserLocal?.({ nama: form.nama })
}

async function onFile(e) {
  const file = e.target.files[0]
  if (!file) return
  if (!file.type.startsWith('image/')) {
    errorMsg.value = 'File harus berupa gambar!'
    return
  }
  const reader = new FileReader()
  reader.onload = async () => {
    form.foto = reader.result
    auth.updateUserLocal?.({ foto: reader.result })
    try {
      await api.post('/profil/foto', { foto_base64: reader.result })
      successMsg.value = 'Foto profil berhasil diperbarui!'
    } catch {
      errorMsg.value = 'Gagal upload foto.'
    }
  }
  reader.readAsDataURL(file)
}

async function hapusFoto() {
  form.foto = ''
  auth.updateUserLocal?.({ foto: '' })
  try {
    await api.delete('/profil/foto')
    successMsg.value = 'Foto profil dihapus.'
  } catch {
    errorMsg.value = 'Gagal menghapus foto.'
  }
}

async function simpan() {
  if (pwdMismatch.value || pwdTooShort.value) return
  loading.value    = true
  successMsg.value = ''
  errorMsg.value   = ''
  try {
    const payload = { nama: form.nama, nim_nip: form.nim_nip, no_hp: form.no_hp }
    if (form.password) {
      payload.password              = form.password
      payload.password_confirmation = form.password_confirmation
    }
    const { data } = await api.put('/profil', payload)
    auth.updateUserLocal?.(data)
    form.password              = ''
    form.password_confirmation = ''
    successMsg.value = 'Profil berhasil disimpan!'
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Gagal menyimpan profil.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.page-heading { margin-bottom: 24px; }
.page-heading h1 { font-size: 22px; font-weight: 700; color: #0f1f3d; margin-bottom: 4px; }
.page-heading p  { font-size: 14px; color: #64748b; }

.grid-2 { display: grid; grid-template-columns: 1fr 1.6fr; gap: 24px; align-items: start; }

.card { background: #fff; border-radius: 10px; padding: 28px; box-shadow: 0 2px 16px rgba(15,31,61,.08); }
.card-center { text-align: center; }
.card-title  { display: flex; align-items: center; gap: 8px; font-size: 16px; font-weight: 700; color: #0f1f3d; margin-bottom: 20px; }

/* Avatar */
.avatar-wrap { display: flex; justify-content: center; margin-bottom: 16px; }
.avatar-img  { width: 96px; height: 96px; border-radius: 50%; object-fit: cover; border: 3px solid #2563eb; }
.avatar-letter {
  width: 96px; height: 96px; border-radius: 50%;
  background: #2563eb; color: #fff;
  font-size: 36px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}

.pf-name  { font-size: 20px; font-weight: 700; color: #0f1f3d; }
.pf-role  { color: #64748b; margin-top: 6px; font-size: 13px; }
.pf-sub   { font-size: 13px; margin-top: 4px; color: #0f1f3d; }
.pf-email { font-size: 13px; color: #64748b; margin-top: 4px; }
.avatar-actions { display: flex; justify-content: center; gap: 8px; margin-top: 20px; flex-wrap: wrap; }

/* Form */
.form-group { margin-bottom: 16px; }
.form-group label { display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: #0f1f3d; margin-bottom: 5px; }

/* Input with eye toggle */
.input-wrap { position: relative; display: flex; align-items: center; }
.input-wrap input { width: 100%; padding: 10px 38px 10px 13px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; font-family: inherit; outline: none; box-sizing: border-box; }
.input-wrap input:focus { border-color: #2563eb; }
.eye-btn { position: absolute; right: 11px; background: none; border: none; cursor: pointer; color: #94a3b8; display: flex; padding: 0; transition: color .2s; }
.eye-btn:hover { color: #2563eb; }

.form-group > input {
  width: 100%; padding: 10px 13px;
  border: 2px solid #e2e8f0; border-radius: 8px;
  font-size: 14px; font-family: inherit; outline: none;
  box-sizing: border-box;
}
.form-group > input:focus { border-color: #2563eb; }
.input-disabled { background: #f0f4f8 !important; color: #94a3b8; cursor: not-allowed; }
.form-hint  { display: flex; align-items: center; gap: 4px; font-size: 11px; color: #94a3b8; margin-top: 4px; }
.form-error { display: flex; align-items: center; gap: 4px; font-size: 11px; color: #dc2626; margin-top: 4px; font-weight: 600; }

.divider { height: 1px; background: #e2e8f0; margin: 20px 0; }
.section-label { font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 14px; }

/* Btn */
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all .2s; }
.btn:disabled { opacity: .6; cursor: not-allowed; }
.btn-primary { background: #0f1f3d; color: #fff; }
.btn-primary:hover:not(:disabled) { background: #1a3260; }
.btn-outline { background: transparent; border: 2px solid #e2e8f0; color: #0f1f3d; }
.btn-outline:hover { border-color: #0f1f3d; }
.btn-danger  { background: #fee2e2; color: #991b1b; }
.btn-danger:hover { background: #fecaca; }
.btn-sm { padding: 7px 13px; font-size: 12px; }

/* Alert */
.alert-success { display: flex; align-items: center; gap: 8px; margin-top: 14px; padding: 10px 14px; background: #d1fae5; color: #065f46; border-radius: 8px; font-size: 13px; }
.alert-error   { display: flex; align-items: center; gap: 8px; margin-top: 14px; padding: 10px 14px; background: #fee2e2; color: #991b1b; border-radius: 8px; font-size: 13px; }

@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin .8s linear infinite; }
</style>