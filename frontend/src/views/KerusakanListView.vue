<template>
  <div>
    <div class="page-heading">
      <WrenchIcon class="heading-icon" />
      <h1>Laporan Kerusakan Aset</h1>
    </div>

    <div class="card">
      <div class="card-title mb-4">
        <ClipboardList class="icon-sm" /> Daftar Kerusakan
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th><Hash class="icon-xs" /> ID</th>
              <th><Package class="icon-xs" /> Aset</th>
              <th><MapPin class="icon-xs" /> Ruangan</th>
              <th><FileText class="icon-xs" /> Deskripsi</th>
              <th><Gauge class="icon-xs" /> Tingkat</th>
              <th><User class="icon-xs" /> Dilaporkan Oleh</th>
              <th><Activity class="icon-xs" /> Status</th>
              <th v-if="canHandle"><Settings2 class="icon-xs" /> Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="k in data.kerusakan" :key="k.id">
              <td><code class="id-code">#{{ k.id }}</code></td>
              <td><b>{{ k.inventaris?.nama ?? '-' }}</b></td>
              <td>
                <span class="lokasi-wrap">
                  <MapPin class="icon-xs text-muted" />
                  {{ k.inventaris?.ruangan?.nama ?? '-' }}
                </span>
              </td>
              <td class="deskripsi-cell">{{ k.deskripsi }}</td>
              <td>
                <span class="badge badge-amber">
                  <Flame class="badge-icon" /> {{ k.tingkat }}
                </span>
              </td>
              <td>{{ k.user?.nama ?? '-' }}</td>
              <td>
                <span class="badge" :class="badgeStatus(k.status)">
                  <CircleCheck v-if="k.status === 'Selesai'"           class="badge-icon" />
                  <LoaderCircle v-else-if="k.status === 'Dalam Perbaikan'" class="badge-icon" />
                  <CircleX v-else                                       class="badge-icon" />
                  {{ k.status }}
                </span>
              </td>
              <td v-if="canHandle">
                <button v-if="k.status !== 'Selesai'" class="btn btn-sm btn-success" @click="tangani(k.id)">
                  <WrenchIcon class="icon-btn" /> Tangani
                </button>
                <span v-else class="selesai-label">
                  <CircleCheck class="icon-xs" /> Selesai
                </span>
              </td>
            </tr>
            <tr v-if="data.kerusakan.length === 0">
              <td :colspan="canHandle ? 8 : 7" class="empty-cell">
                <PackageX class="empty-icon" />
                <span>Belum ada laporan kerusakan.</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import api from '@/api'
import { useDataStore } from '@/stores/data'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'
import {
  Wrench as WrenchIcon, ClipboardList,
  Hash, Package, PackageX, MapPin, FileText,
  Gauge, User, Activity, Settings2, Flame,
  CircleCheck, CircleX, LoaderCircle
} from 'lucide-vue-next'

const data = useDataStore(); const auth = useAuthStore(); const ui = useUiStore()
const canHandle = computed(() => ['aslab', 'admin'].includes(auth.role))

onMounted(() => data.fetchKerusakan().catch(() => {}))

function badgeStatus(s) {
  if (s === 'Selesai')         return 'badge-green'
  if (s === 'Dalam Perbaikan') return 'badge-amber'
  return 'badge-red'
}

async function tangani(id) {
  try {
    await api.post(`/kerusakan/${id}/tangani`)
    await data.fetchKerusakan()
    ui.showSuccess('Berhasil', 'Kerusakan berhasil ditangani.')
  } catch {
    ui.showError('Gagal', 'Gagal menangani kerusakan.')
  }
}
</script>

<style scoped>
/* Heading */
.page-heading { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
.page-heading h1 { font-size: 22px; font-weight: 700; color: #0f1f3d; }
.heading-icon { width: 26px; height: 26px; color: #d97706; flex-shrink: 0; }

/* Card */
.card { background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 16px rgba(15,31,61,.08); }
.card-title { font-size: 15px; font-weight: 700; color: #0f1f3d; display: flex; align-items: center; gap: 7px; }
.mb-4 { margin-bottom: 16px; }

/* Table */
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th { text-align: left; padding: 8px 10px; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; white-space: nowrap; }
td { padding: 11px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

/* ID code */
.id-code { font-size: 11px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #475569; }

/* Lokasi & deskripsi */
.lokasi-wrap { display: inline-flex; align-items: center; gap: 4px; color: #64748b; }
.text-muted { color: #94a3b8; }
.deskripsi-cell { max-width: 180px; font-size: 12px; color: #64748b; }

/* Badge */
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-icon { width: 11px; height: 11px; }
.badge-green { background: #d1fae5; color: #065f46; }
.badge-amber { background: #fef3c7; color: #92400e; }
.badge-red   { background: #fee2e2; color: #991b1b; }

/* Selesai label */
.selesai-label { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; color: #059669; }

/* Empty state */
.empty-cell { text-align: center; color: #94a3b8; padding: 36px !important; }
.empty-cell { display: flex; flex-direction: column; align-items: center; gap: 10px; }
.empty-icon { width: 34px; height: 34px; color: #cbd5e1; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; transition: all .2s; }
.btn-sm { padding: 5px 10px; }
.btn-success { background: #d1fae5; color: #065f46; }
.btn-success:hover { background: #a7f3d0; }

/* Icon sizes */
.icon-xs  { width: 13px; height: 13px; }
.icon-sm  { width: 16px; height: 16px; }
.icon-btn { width: 13px; height: 13px; }
</style>