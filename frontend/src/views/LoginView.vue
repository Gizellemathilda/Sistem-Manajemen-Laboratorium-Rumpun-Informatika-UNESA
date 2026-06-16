<template>
  <div class="login-screen">
    <!-- KIRI: Branding -->
    <div class="login-left">
      <div class="login-brand">
        <div class="logo-box">
          <FlaskConical :size="26" color="#fff" />
        </div>
        <div>
          <span>SIM-LAB</span>
          <small>Universitas Negeri Surabaya</small>
        </div>
      </div>

      <div class="login-tagline">
        Kelola Laboratorium<br>
        Lebih <span>Cerdas</span> &amp; Transparan
      </div>

      <p class="login-desc">
        Sistem Informasi Manajemen Laboratorium untuk peminjaman,
        inventaris, jadwal, dan monitoring perangkat secara real-time.
      </p>

      <div class="login-features">
        <div class="feat"><CalendarCheck :size="15" class="feat-icon" /> Booking ruang &amp; alat secara online</div>
        <div class="feat"><BarChart2 :size="15" class="feat-icon" /> Monitoring inventaris real-time</div>
        <div class="feat"><Wrench :size="15" class="feat-icon" /> Laporan kerusakan &amp; maintenance</div>
        <div class="feat"><ClipboardList :size="15" class="feat-icon" /> Manajemen nilai &amp; absensi praktikum</div>
        <div class="feat"><Bell :size="15" class="feat-icon" /> Notifikasi otomatis multi-peran</div>
      </div>
    </div>

    <!-- KANAN: Form Login -->
    <div class="login-right">
      <div class="login-card">
        <h2>Masuk ke SIM-LAB</h2>
        <p>Gunakan akun institusi Anda untuk mengakses sistem.</p>

        <div v-if="error" class="login-error">
          <AlertCircle :size="14" style="flex-shrink:0" />
          {{ error }}
        </div>

        <form @submit.prevent="submit">
          <div class="form-group">
            <label>Email</label>
            <div class="input-wrapper">
              <Mail :size="16" class="input-icon" />
              <input type="email" v-model="email" placeholder="email@unesa.ac.id" required />
            </div>
          </div>

          <div class="form-group">
            <label>Password</label>
            <div class="input-wrapper">
              <Lock :size="16" class="input-icon" />
              <input
                :type="showPassword ? 'text' : 'password'"
                v-model="password"
                placeholder="••••••••"
                required
              />
              <button type="button" class="eye-btn" @click="showPassword = !showPassword" tabindex="-1">
                <Eye v-if="!showPassword" :size="16" />
                <EyeOff v-else :size="16" />
              </button>
            </div>
          </div>

          <div class="form-group">
            <label>Login sebagai</label>
            <div class="input-wrapper">
              <Users :size="16" class="input-icon" />
              <select v-model="role">
                <option value="mahasiswa">Mahasiswa</option>
                <option value="asprak">Asisten Praktikum</option>
                <option value="aslab">Asisten Lab</option>
                <option value="dosen">Dosen</option>
                <option value="admin">Admin (Pengelola)</option>
              </select>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-full" :disabled="loading">
            <Loader2 v-if="loading" :size="16" class="spin" />
            <LogIn v-else :size="16" />
            {{ loading ? 'Memproses…' : 'Masuk' }}
          </button>
        </form>

        <div class="login-demo">
          <h4><Info :size="12" style="display:inline;vertical-align:middle;margin-right:4px" />Demo Credentials (password: <b>password</b>)</h4>
          <div class="cred">Mahasiswa: <b>mahasiswa@unesa.ac.id</b></div>
          <div class="cred">Asprak: <b>asprak@unesa.ac.id</b></div>
          <div class="cred">Aslab: <b>aslab@unesa.ac.id</b></div>
          <div class="cred">Dosen: <b>dosen@unesa.ac.id</b></div>
          <div class="cred">Admin: <b>admin@unesa.ac.id</b></div>
        </div>

        <div class="login-register">
          Belum punya akun?
          <router-link to="/register">Buat akun baru</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  FlaskConical,
  CalendarCheck,
  BarChart2,
  Wrench,
  ClipboardList,
  Bell,
  Mail,
  Lock,
  Eye,
  EyeOff,
  Users,
  LogIn,
  Loader2,
  AlertCircle,
  Info,
} from 'lucide-vue-next'

const email        = ref('')
const password     = ref('')
const role         = ref('mahasiswa')
const loading      = ref(false)
const error        = ref('')
const showPassword = ref(false)

const router = useRouter()
const auth   = useAuthStore()

async function submit() {
  loading.value = true
  error.value   = ''
  try {
    await auth.login(email.value, password.value, role.value)
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message || e.response?.data?.errors?.email?.[0] || 'Login gagal. Periksa kembali email dan password Anda.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap');

* { box-sizing: border-box; margin: 0; padding: 0; }

.login-screen {
  display: flex;
  min-height: 100vh;
  font-family: 'IBM Plex Sans', sans-serif;
  background: linear-gradient(135deg, #0f1f3d 0%, #243a6e 60%, #1e40af 100%);
}

/* ─── KIRI ─── */
.login-left {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 60px 80px;
  color: #fff;
}
.login-brand { display: flex; align-items: center; gap: 14px; margin-bottom: 48px; }
.login-brand .logo-box {
  width: 52px; height: 52px;
  background: #10b981; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
}
.login-brand span { font-size: 22px; font-weight: 700; letter-spacing: -.5px; }
.login-brand small { display: block; font-size: 11px; opacity: .6; font-weight: 400; margin-top: 2px; letter-spacing: .5px; text-transform: uppercase; }
.login-tagline { font-size: 42px; font-weight: 700; line-height: 1.15; margin-bottom: 20px; letter-spacing: -1px; }
.login-tagline span { color: #10b981; }
.login-desc { font-size: 15px; opacity: .7; line-height: 1.7; max-width: 400px; }
.login-features { margin-top: 40px; display: flex; flex-direction: column; gap: 12px; }
.login-features .feat { display: flex; align-items: center; gap: 10px; font-size: 13px; opacity: .8; }
.login-features .feat .feat-icon { color: #10b981; flex-shrink: 0; }

/* ─── KANAN ─── */
.login-right { width: 480px; background: #f1f5fb; display: flex; align-items: center; justify-content: center; padding: 40px; }
.login-card { width: 100%; max-width: 380px; }
.login-card h2 { font-size: 26px; font-weight: 700; margin-bottom: 6px; color: #0f1f3d; }
.login-card > p { font-size: 14px; color: #64748b; margin-bottom: 32px; }
.login-error {
  display: flex; align-items: center; gap: 8px;
  background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c;
  padding: 10px 14px; border-radius: 10px; font-size: 13px; margin-bottom: 16px;
}
.form-group { margin-bottom: 18px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: #0f1f3d; margin-bottom: 6px; }

/* Input wrapper dengan icon */
.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}
.input-wrapper .input-icon {
  position: absolute;
  left: 13px;
  color: #94a3b8;
  pointer-events: none;
  z-index: 1;
}
.input-wrapper input,
.input-wrapper select {
  width: 100%;
  padding: 11px 14px 11px 38px;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-family: inherit; font-size: 14px;
  color: #0f1f3d; background: #fff;
  outline: none;
  transition: border-color .2s;
  appearance: auto;
}
.input-wrapper input:focus,
.input-wrapper select:focus { border-color: #2563eb; }

/* Tombol show/hide password */
.eye-btn {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  display: flex;
  align-items: center;
  padding: 0;
  transition: color .2s;
}
.eye-btn:hover { color: #2563eb; }

.btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  padding: 12px 20px; border-radius: 10px;
  font-family: inherit; font-size: 14px; font-weight: 600;
  cursor: pointer; border: none; transition: all .2s;
}
.btn:disabled { opacity: .6; cursor: not-allowed; }
.btn-primary { background: #0f1f3d; color: #fff; }
.btn-primary:hover:not(:disabled) { background: #1a3260; }
.btn-full { width: 100%; margin-top: 4px; }

/* Spinner */
@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin .8s linear infinite; }

.login-demo { margin-top: 28px; padding: 16px; background: #f0f4ff; border-radius: 10px; border-left: 3px solid #2563eb; }
.login-demo h4 { font-size: 12px; font-weight: 700; color: #2563eb; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 10px; }
.login-demo .cred { font-size: 11.5px; color: #64748b; margin-bottom: 4px; }
.login-demo .cred b { color: #0f1f3d; }
.login-register { margin-top: 18px; text-align: center; font-size: 13px; color: #64748b; }
.login-register a { color: #2563eb; font-weight: 600; text-decoration: none; }
.login-register a:hover { text-decoration: underline; }
</style>