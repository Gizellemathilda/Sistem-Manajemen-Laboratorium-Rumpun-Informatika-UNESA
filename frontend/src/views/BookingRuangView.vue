<template>
  <div>
    <div class="page-heading">
      <Building2 class="heading-icon" />
      <div>
        <h1>Booking Ruangan Lab</h1>
        <p>Pesan ruangan laboratorium untuk kebutuhan kuliah atau tugas akhir.</p>
      </div>
    </div>

    <div class="grid grid-2">
      <!-- Form booking -->
      <div class="card">
        <div class="card-title mb-4">
          <ClipboardEdit class="icon-sm" />
          Form Booking Ruangan
        </div>

        <div class="form-group">
          <label><DoorOpen class="icon-xs" /> Ruangan</label>
          <div class="input-wrap">
            <select v-model="form.ruangan_id">
              <option value="">-- Pilih Ruangan --</option>
              <option v-for="r in ruanganTersedia" :key="r.id" :value="r.id">{{ r.nama }}</option>
            </select>
            <ChevronDown class="select-arrow" />
          </div>
          <div v-if="!ruanganTersedia.length" class="hint">
            <Info class="icon-xs" /> Tidak ada ruangan tersedia.
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label><CalendarDays class="icon-xs" /> Tanggal</label>
            <div class="input-wrap">
              <Calendar class="input-icon" />
              <input type="date" v-model="form.tanggal" :min="today" class="has-icon" />
            </div>
          </div>
          <div class="form-group">
            <label><Clock class="icon-xs" /> Waktu</label>
            <div class="input-wrap">
              <Timer class="input-icon" />
              <input type="text" v-model="form.waktu" placeholder="08:00-10:00" class="has-icon" />
            </div>
          </div>
        </div>

        <div class="form-group">
          <label><FileText class="icon-xs" /> Keperluan</label>
          <div class="textarea-wrap">
            <textarea v-model="form.keperluan" rows="3" placeholder="Praktikum/Tugas Akhir..."></textarea>
          </div>
        </div>

        <button class="btn btn-primary btn-full" :disabled="loading || !form.ruangan_id || !form.waktu" @click="ajukan">
          <Loader2 v-if="loading" class="icon-btn spinning" />
          <Send v-else class="icon-btn" />
          {{ loading ? 'Mengajukan…' : 'Ajukan Booking' }}
        </button>
      </div>

      <!-- Status ketersediaan -->
      <div class="card">
        <div class="card-title mb-4">
          <LayoutGrid class="icon-sm" />
          Status Ketersediaan Ruangan
        </div>
        <div v-for="r in data.ruangan" :key="r.id" class="ruangan-row">
          <div class="ruangan-info">
            <Building2 class="ruangan-icon" />
            <div>
              <div class="ruangan-nama">{{ r.nama }}</div>
              <div class="ruangan-meta">
                <MapPin class="icon-xs" /> {{ r.lokasi }}
                <span class="dot">•</span>
                <Users class="icon-xs" /> {{ r.kapasitas }} orang
              </div>
            </div>
          </div>
          <span class="badge" :class="r.status === 'Tersedia' ? 'badge-green' : 'badge-red'">
            <CircleCheck v-if="r.status === 'Tersedia'" class="badge-icon" />
            <CircleX v-else class="badge-icon" />
            {{ r.status }}
          </span>
        </div>
        <div v-if="!data.ruangan.length" class="empty-state">
          <Building class="empty-icon" />
          <span>Belum ada data ruangan.</span>
        </div>
      </div>
    </div>

    <!-- Riwayat booking -->
    <div class="card mt-4">
      <div class="card-title mb-4">
        <History class="icon-sm" />
        Riwayat Booking Ruangan
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th><User class="icon-xs" /> Pemohon</th>
              <th><DoorOpen class="icon-xs" /> Ruangan</th>
              <th><CalendarDays class="icon-xs" /> Tanggal</th>
              <th><Clock class="icon-xs" /> Waktu</th>
              <th><FileText class="icon-xs" /> Keperluan</th>
              <th><Activity class="icon-xs" /> Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in bookingRuang" :key="b.id">
              <td>{{ b.user?.nama ?? '-' }}</td>
              <td>{{ b.ruangan?.nama ?? '-' }}</td>
              <td>{{ b.tanggal }}</td>
              <td>{{ b.waktu }}</td>
              <td>{{ b.keperluan }}</td>
              <td>
                <span class="badge" :class="badgeStatus(b.status)">
                  <CircleCheck v-if="b.status === 'Disetujui'" class="badge-icon" />
                  <Clock v-else-if="b.status === 'Menunggu'" class="badge-icon" />
                  <CircleX v-else class="badge-icon" />
                  {{ b.status }}
                </span>
              </td>
            </tr>
            <tr v-if="!bookingRuang.length">
              <td colspan="6" class="empty-cell">
                <CalendarOff class="empty-icon" />
                <span>Belum ada riwayat booking.</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import api from '@/api'
import { useDataStore } from '@/stores/data'
import { useUiStore } from '@/stores/ui'
import {
  Building2, Building, ClipboardEdit, DoorOpen, LayoutGrid,
  CalendarDays, Calendar, CalendarOff, Clock, Timer,
  FileText, MapPin, Users, User, Activity, History,
  ChevronDown, Info, Send, Loader2,
  CircleCheck, CircleX
} from 'lucide-vue-next'

const data = useDataStore(); const ui = useUiStore()
const loading = ref(false)
const bookingRuang = ref([])
const today = new Date().toISOString().slice(0, 10)
const form = reactive({ ruangan_id: '', tanggal: today, waktu: '', keperluan: '' })

const ruanganTersedia = computed(() => data.ruangan?.filter(r => r.status === 'Tersedia') || [])

onMounted(async () => {
  await data.fetchRuangan?.().catch(() => {})
  await fetchBooking()
})

async function fetchBooking() {
  try {
    const { data: d } = await api.get('/booking-ruang')
    bookingRuang.value = d
  } catch {}
}

function badgeStatus(s) {
  if (s === 'Disetujui') return 'badge-green'
  if (s === 'Menunggu')  return 'badge-amber'
  return 'badge-red'
}

async function ajukan() {
  if (!form.ruangan_id) { ui.showError('Gagal', 'Pilih ruangan!'); return }
  if (!form.waktu)      { ui.showError('Gagal', 'Isi waktu booking!'); return }
  loading.value = true
  try {
    await api.post('/booking-ruang', { ...form })
    await fetchBooking()
    ui.showSuccess('Berhasil', 'Booking ruangan berhasil diajukan!')
    Object.assign(form, { ruangan_id: '', tanggal: today, waktu: '', keperluan: '' })
  } catch (e) {
    ui.showError('Gagal', e.response?.data?.message || 'Gagal mengajukan booking.')
  } finally { loading.value = false }
}
</script>

<style scoped>
/* Heading */
.page-heading { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 20px; }
.page-heading h1 { font-size: 22px; font-weight: 700; color: #0f1f3d; margin-bottom: 4px; }
.page-heading p  { font-size: 14px; color: #64748b; }
.heading-icon { width: 28px; height: 28px; color: #2563eb; margin-top: 2px; flex-shrink: 0; }

/* Grid */
.grid { display: grid; gap: 20px; }
.grid-2 { grid-template-columns: 1fr 1fr; }
.mt-4 { margin-top: 16px; }

/* Card */
.card { background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 16px rgba(15,31,61,.08); }
.card-title { font-size: 15px; font-weight: 700; color: #0f1f3d; display: flex; align-items: center; gap: 7px; }
.mb-4 { margin-bottom: 16px; }

/* Form */
.form-group { margin-bottom: 14px; }
.form-group label { display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: #0f1f3d; margin-bottom: 5px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.hint { display: flex; align-items: center; gap: 4px; font-size: 11px; color: #94a3b8; margin-top: 4px; }

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
.input-icon {
  position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
  width: 15px; height: 15px; color: #94a3b8; pointer-events: none;
}
.select-arrow {
  position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
  width: 15px; height: 15px; color: #94a3b8; pointer-events: none;
}

/* Textarea */
.textarea-wrap textarea {
  width: 100%; padding: 9px 12px; border: 2px solid #e2e8f0;
  border-radius: 8px; font-size: 14px; font-family: inherit; outline: none;
  resize: vertical; box-sizing: border-box;
}
.textarea-wrap textarea:focus { border-color: #2563eb; }

/* Ruangan rows */
.ruangan-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
.ruangan-info { display: flex; align-items: center; gap: 10px; }
.ruangan-icon { width: 18px; height: 18px; color: #2563eb; flex-shrink: 0; }
.ruangan-nama { font-size: 14px; font-weight: 600; color: #0f1f3d; }
.ruangan-meta { display: flex; align-items: center; gap: 4px; font-size: 12px; color: #64748b; margin-top: 2px; }
.dot { margin: 0 2px; }

/* Empty states */
.empty-state { display: flex; flex-direction: column; align-items: center; gap: 8px; text-align: center; color: #94a3b8; padding: 20px; }
.empty-icon { width: 32px; height: 32px; color: #cbd5e1; }
.empty-cell { text-align: center; color: #94a3b8; padding: 28px !important; }
.empty-cell { display: flex; flex-direction: column; align-items: center; gap: 8px; }

/* Table */
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th { text-align: left; padding: 8px 10px; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; white-space: nowrap; }
td { padding: 11px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

/* Badge */
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-icon { width: 12px; height: 12px; }
.badge-green { background: #d1fae5; color: #065f46; }
.badge-amber { background: #fef3c7; color: #92400e; }
.badge-red   { background: #fee2e2; color: #991b1b; }

/* Buttons */
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 10px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; transition: all .2s; }
.btn:disabled { opacity: .6; cursor: not-allowed; }
.btn-primary { background: #0f1f3d; color: #fff; }
.btn-primary:hover:not(:disabled) { background: #1a3260; }
.btn-full { width: 100%; }

/* Icon sizes */
.icon-xs  { width: 13px; height: 13px; }
.icon-sm  { width: 16px; height: 16px; }
.icon-btn { width: 15px; height: 15px; }

/* Spinner */
.spinning { animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>