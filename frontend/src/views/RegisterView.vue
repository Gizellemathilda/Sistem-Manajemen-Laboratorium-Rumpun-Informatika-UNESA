<template>
  <div class="register-screen">
    <div class="register-card">

      <!-- Brand -->
      <div class="brand">
        <div class="logo-icon">
          <FlaskConical :size="22" color="#fff" />
        </div>
        <div>
          <div class="logo-text">SIM-LAB</div>
          <div class="logo-sub">Buat Akun Baru</div>
        </div>
      </div>

      <h2>Daftarkan Akun Anda</h2>
      <p>Isi data berikut untuk membuat akun baru SIM-LAB.</p>

      <div v-if="error" class="error-box">
        <AlertCircle :size="14" style="flex-shrink:0" /> {{ error }}
      </div>

      <div class="form-group">
        <label><UserRound :size="12" /> Nama Lengkap</label>
        <div class="input-wrap">
          <UserRound :size="15" class="input-icon" />
          <input v-model="form.nama" type="text" placeholder="Mis. Budi Santoso" required />
        </div>
      </div>

      <div class="form-group">
        <label><Mail :size="12" /> Email Institusi</label>
        <div class="input-wrap">
          <Mail :size="15" class="input-icon" />
          <input v-model="form.email" type="email" placeholder="nama@unesa.ac.id" required />
        </div>
      </div>

      <div class="form-group">
        <label><Hash :size="12" /> NIM / NIP</label>
        <div class="input-wrap">
          <Hash :size="15" class="input-icon" />
          <input v-model="form.nim_nip" type="text" placeholder="Mis. 24051204200" />
        </div>
      </div>

      <div class="form-group">
        <label><Users :size="12" /> Daftar sebagai</label>
        <div class="input-wrap">
          <Users :size="15" class="input-icon" />
          <select v-model="form.role">
            <option value="mahasiswa">Mahasiswa</option>
            <option value="asprak">Asisten Praktikum</option>
            <option value="aslab">Asisten Lab</option>
            <option value="dosen">Dosen</option>
            <option value="admin">Admin (Pengelola)</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label><Lock :size="12" /> Password</label>
        <div class="input-wrap">
          <Lock :size="15" class="input-icon" />
          <input
            v-model="form.password"
            :type="showPwd ? 'text' : 'password'"
            placeholder="Min. 6 karakter"
            required
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
          <LockKeyhole :size="15" class="input-icon" />
          <input
            v-model="form.password_confirmation"
            :type="showPwdC ? 'text' : 'password'"
            placeholder="Ulangi password"
            required
          />
          <button type="button" class="eye-btn" @click="showPwdC = !showPwdC" tabindex="-1">
            <Eye v-if="!showPwdC" :size="15" />
            <EyeOff v-else :size="15" />
          </button>
        </div>
        <div v-if="pwdMismatch" class="field-error">
          <AlertCircle :size="11" /> Password dan konfirmasi tidak sama.
        </div>
      </div>

      <button class="btn btn-primary btn-full" @click="submit" :disabled="loading || pwdMismatch">
        <Loader2 v-if="loading" :size="15" class="spin" />
        <UserPlus v-else :size="15" />
        {{ loading ? 'Memproses…' : 'Daftar Sekarang' }}
      </button>

      <div class="login-link">
        Sudah punya akun?
        <router-link to="/login">Masuk di sini</router-link>
      </div>

    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/api'
import {
  FlaskConical,
  UserRound,
  UserPlus,
  Mail,
  Hash,
  Users,
  Lock,
  LockKeyhole,
  Eye,
  EyeOff,
  Loader2,
  AlertCircle,
} from 'lucide-vue-next'

const router = useRouter()
const auth   = useAuthStore()

const loading  = ref(false)
const error    = ref('')
const showPwd  = ref(false)
const showPwdC = ref(false)

const form = reactive({
  nama: '', email: '', nim_nip: '',
  role: 'mahasiswa',
  password: '', password_confirmation: '',
})

const pwdMismatch = computed(() =>
  !!form.password && !!form.password_confirmation &&
  form.password !== form.password_confirmation
)

async function submit() {
  if (pwdMismatch.value) return
  error.value   = ''
  loading.value = true
  try {
    const { data } = await api.post('/register', {
      nama:                  form.nama,
      email:                 form.email,
      nim_nip:               form.nim_nip || null,
      role:                  form.role,
      password:              form.password,
      password_confirmation: form.password_confirmation,
    })
    auth.user  = data.user
    auth.token = data.token
    localStorage.setItem('simlab_token', data.token)
    localStorage.setItem('simlab_user', JSON.stringify(data.user))
    router.push('/')
  } catch (e) {
    const resp = e.response?.data
    if (resp?.errors) {
      error.value = Object.values(resp.errors).flat().join(' ')
    } else {
      error.value = resp?.message || 'Pendaftaran gagal. Coba lagi.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap');

.register-screen {
  min-height: 100vh;
  display: flex; align-items: center; justify-content: center;
  background: linear-gradient(135deg, #0f1f3d 0%, #243a6e 60%, #1e40af 100%);
  padding: 40px 20px;
  font-family: 'IBM Plex Sans', sans-serif;
}

.register-card {
  width: 100%; max-width: 460px;
  background: #fff; border-radius: 14px;
  box-shadow: 0 8px 32px rgba(15,31,61,.2);
  padding: 36px;
}

/* Brand */
.brand { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
.logo-icon {
  width: 44px; height: 44px; background: #10b981;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
}
.logo-text { font-size: 18px; font-weight: 700; color: #0f1f3d; }
.logo-sub  { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-top: 1px; }

h2 { font-size: 22px; font-weight: 700; color: #0f1f3d; margin: 0 0 6px; }
p  { font-size: 13px; color: #64748b; margin: 0 0 24px; }

/* Error */
.error-box {
  display: flex; align-items: center; gap: 8px;
  background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c;
  padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px;
}
.field-error { display: flex; align-items: center; gap: 4px; font-size: 11px; color: #dc2626; margin-top: 4px; font-weight: 600; }

/* Form */
.form-group { margin-bottom: 16px; }
.form-group label { display: flex; align-items: center; gap: 5px; font-size: 13px; font-weight: 600; color: #0f1f3d; margin-bottom: 6px; }

/* Input wrapper */
.input-wrap { position: relative; display: flex; align-items: center; }
.input-icon { position: absolute; left: 12px; color: #94a3b8; pointer-events: none; flex-shrink: 0; }
.input-wrap input,
.input-wrap select {
  width: 100%;
  padding: 10px 13px 10px 36px;
  border: 2px solid #e2e8f0; border-radius: 8px;
  font-family: inherit; font-size: 14px;
  color: #0f1f3d; outline: none;
  transition: border-color .2s;
  box-sizing: border-box;
  appearance: auto;
}
.input-wrap input:focus,
.input-wrap select:focus { border-color: #2563eb; }

/* Eye toggle */
.eye-btn {
  position: absolute; right: 11px;
  background: none; border: none; cursor: pointer;
  color: #94a3b8; display: flex; padding: 0;
  transition: color .2s;
}
.eye-btn:hover { color: #2563eb; }

/* Button */
.btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  padding: 12px 20px; border-radius: 8px;
  font-family: inherit; font-size: 14px; font-weight: 600;
  cursor: pointer; border: none; transition: all .2s;
}
.btn:disabled { opacity: .6; cursor: not-allowed; }
.btn-primary { background: #0f1f3d; color: #fff; }
.btn-primary:hover:not(:disabled) { background: #1a3260; }
.btn-full { width: 100%; margin-top: 8px; padding: 13px; }

/* Login link */
.login-link { margin-top: 18px; text-align: center; font-size: 13px; color: #64748b; }
.login-link a { color: #2563eb; font-weight: 600; text-decoration: none; }
.login-link a:hover { text-decoration: underline; }

@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin .8s linear infinite; }
</style>