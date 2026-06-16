<template>
  <div>
    <div class="page-heading">
      <Wrench class="heading-icon" />
      <h1>Lapor Kerusakan</h1>
    </div>

    <div class="card">
      <div class="form-group">
        <label><Package class="icon-xs" /> Aset</label>
        <div class="input-wrap">
          <select v-model="form.inventaris_id">
            <option value="">-- Pilih Aset --</option>
            <option v-for="i in data.inventaris" :key="i.id" :value="i.id">
              {{ i.nama }} — {{ i.ruangan?.nama ?? '-' }}
            </option>
          </select>
          <ChevronDown class="select-arrow" />
        </div>
      </div>

      <div class="form-group">
        <label><Gauge class="icon-xs" /> Tingkat Kerusakan</label>
        <div class="input-wrap">
          <select v-model="form.tingkat">
            <option value="Ringan">Ringan</option>
            <option value="Sedang">Sedang</option>
            <option value="Berat">Berat</option>
          </select>
          <ChevronDown class="select-arrow" />
        </div>
        <div class="tingkat-hint">
          <span class="badge" :class="tingkatBadge">
            <Flame class="badge-icon" /> {{ form.tingkat }}
          </span>
        </div>
      </div>

      <div class="form-group">
        <label><FileText class="icon-xs" /> Deskripsi Kerusakan</label>
        <textarea v-model="form.deskripsi" rows="4" placeholder="Jelaskan kerusakan secara detail..."></textarea>
      </div>

      <button class="btn btn-primary btn-full" :disabled="loading || !form.inventaris_id" @click="submit">
        <Loader2 v-if="loading" class="icon-btn spinning" />
        <Send v-else class="icon-btn" />
        {{ loading ? 'Mengirim…' : 'Kirim Laporan' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import api from '@/api'
import { useDataStore } from '@/stores/data'
import { useUiStore } from '@/stores/ui'
import {
  Wrench, Package, Gauge, Flame,
  FileText, ChevronDown, Send, Loader2
} from 'lucide-vue-next'

const data = useDataStore(); const ui = useUiStore()
const loading = ref(false)
const form = reactive({ inventaris_id: '', tingkat: 'Ringan', deskripsi: '' })

onMounted(() => data.fetchInventaris().catch(() => {}))

const tingkatBadge = computed(() => {
  if (form.tingkat === 'Berat')  return 'badge-red'
  if (form.tingkat === 'Sedang') return 'badge-amber'
  return 'badge-green'
})

async function submit() {
  if (!form.inventaris_id) { ui.showError('Gagal', 'Pilih aset terlebih dahulu!'); return }
  loading.value = true
  try {
    await api.post('/kerusakan', {
      inventaris_id: form.inventaris_id,
      deskripsi:     form.deskripsi,
      tingkat:       form.tingkat.toLowerCase(),
    })
    const aset = data.inventaris.find(i => i.id === form.inventaris_id)
    ui.showSuccess('Laporan Terkirim', `Laporan kerusakan berhasil dikirim!\nAset: ${aset?.nama}\nStatus: Menunggu Teknisi`)
    Object.assign(form, { inventaris_id: '', tingkat: 'Ringan', deskripsi: '' })
    data.fetchUnreadCount?.()
  } catch (e) {
    ui.showError('Gagal', e.response?.data?.message || 'Gagal mengirim laporan')
  } finally { loading.value = false }
}
</script>

<style scoped>
/* Heading */
.page-heading { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
.page-heading h1 { font-size: 22px; font-weight: 700; color: #0f1f3d; }
.heading-icon { width: 26px; height: 26px; color: #d97706; flex-shrink: 0; }

/* Card */
.card { background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 16px rgba(15,31,61,.08); max-width: 600px; }

/* Form */
.form-group { margin-bottom: 16px; }
.form-group label { display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: #0f1f3d; margin-bottom: 5px; }
.form-group textarea { width: 100%; padding: 9px 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; font-family: inherit; outline: none; resize: vertical; box-sizing: border-box; }
.form-group textarea:focus { border-color: #2563eb; }

/* Input wrap */
.input-wrap { position: relative; }
.input-wrap select {
  width: 100%; padding: 9px 12px; border: 2px solid #e2e8f0;
  border-radius: 8px; font-size: 14px; font-family: inherit; outline: none;
  appearance: none; -webkit-appearance: none; background: #fff;
  box-sizing: border-box;
}
.input-wrap select:focus { border-color: #2563eb; }
.select-arrow { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: #94a3b8; pointer-events: none; }

/* Tingkat hint */
.tingkat-hint { margin-top: 7px; }

/* Badge */
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-icon { width: 11px; height: 11px; }
.badge-green { background: #d1fae5; color: #065f46; }
.badge-amber { background: #fef3c7; color: #92400e; }
.badge-red   { background: #fee2e2; color: #991b1b; }

/* Button */
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 11px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; transition: all .2s; }
.btn:disabled { opacity: .6; cursor: not-allowed; }
.btn-primary { background: #0f1f3d; color: #fff; }
.btn-primary:hover:not(:disabled) { background: #1a3260; }
.btn-full { width: 100%; }

/* Icon sizes */
.icon-xs  { width: 13px; height: 13px; }
.icon-btn { width: 15px; height: 15px; }

/* Spinner */
.spinning { animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>