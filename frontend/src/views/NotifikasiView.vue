<template>
  <div>
    <div class="page-heading"><h1>Notifikasi</h1></div>
    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <Bell :size="16" /> Semua Notifikasi
        </div>
        <button class="btn btn-outline btn-sm" @click="tandaiSemua" :disabled="data.unreadCount === 0">
          <CheckCheck :size="14" /> Tandai semua sudah dibaca
        </button>
      </div>

      <div v-if="data.notifikasi.length === 0" class="empty-state">
        <BellOff :size="36" class="empty-icon" />
        <p>Tidak ada notifikasi</p>
      </div>

      <div v-for="n in data.notifikasi" :key="n.id" class="notif-item" :class="{ unread: !n.dibaca }">
        <div class="notif-icon-col">
          <component :is="notifIcon(n)" :size="16" class="notif-type-icon" />
        </div>
        <div class="notif-content">
          <div class="notif-title">
            <span v-if="!n.dibaca" class="dot-unread"></span>
            {{ n.judul }}
          </div>
          <div class="notif-desc">{{ n.pesan }}</div>
          <div class="notif-time">
            <Clock :size="11" /> {{ formatTime(n.created_at) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useDataStore } from '@/stores/data'
import { useUiStore } from '@/stores/ui'
import {
  Bell,
  BellOff,
  CheckCheck,
  Clock,
  Info,
  AlertTriangle,
  CheckCircle2,
  CalendarClock,
  Wrench,
  MessageSquare,
} from 'lucide-vue-next'

const data = useDataStore()
const ui   = useUiStore()

onMounted(async () => {
  await data.fetchNotifikasi?.()
})

async function tandaiSemua() {
  await data.tandaiSemuaBaca?.()
  ui.showSuccess('Berhasil', 'Semua notifikasi telah ditandai dibaca.')
}

function formatTime(ts) {
  if (!ts) return ''
  return new Date(ts).toLocaleString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}

// Pilih icon berdasarkan tipe/judul notifikasi
function notifIcon(n) {
  const judul = (n.judul || '').toLowerCase()
  if (judul.includes('jadwal') || judul.includes('booking')) return CalendarClock
  if (judul.includes('kerusakan') || judul.includes('maintenance')) return Wrench
  if (judul.includes('peringatan') || judul.includes('batas')) return AlertTriangle
  if (judul.includes('berhasil') || judul.includes('disetujui')) return CheckCircle2
  if (judul.includes('pesan') || judul.includes('komentar')) return MessageSquare
  return Info
}
</script>

<style scoped>
.page-heading { margin-bottom: 20px; }
.page-heading h1 { font-size: 22px; font-weight: 700; color: #0f1f3d; }

.card { background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 16px rgba(15,31,61,.08); }
.card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.card-title  { display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 700; color: #0f1f3d; }

/* Notif item */
.notif-item {
  display: flex; gap: 12px;
  padding: 14px 0;
  border-bottom: 1px solid #f1f5f9;
}
.notif-item.unread {
  background: #f8faff;
  border-radius: 8px;
  padding: 14px 12px;
  margin-bottom: 4px;
}
.notif-icon-col {
  flex-shrink: 0;
  width: 34px; height: 34px;
  border-radius: 8px;
  background: #f1f5f9;
  display: flex; align-items: center; justify-content: center;
}
.notif-item.unread .notif-icon-col {
  background: #dbeafe;
}
.notif-type-icon { color: #64748b; }
.notif-item.unread .notif-type-icon { color: #2563eb; }

.notif-content { flex: 1; min-width: 0; }
.notif-title { font-size: 14px; font-weight: 600; color: #0f1f3d; display: flex; align-items: center; gap: 8px; }
.dot-unread  { display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #2563eb; flex-shrink: 0; }
.notif-desc  { font-size: 13px; color: #64748b; margin-top: 3px; }
.notif-time  { display: flex; align-items: center; gap: 4px; font-size: 11px; color: #94a3b8; margin-top: 5px; }

/* Empty state */
.empty-state { text-align: center; padding: 48px 20px; color: #94a3b8; }
.empty-icon  { margin: 0 auto 12px; display: block; opacity: .35; }
.empty-state p { font-size: 13px; }

.btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: 2px solid #e2e8f0; background: transparent; color: #0f1f3d; transition: all .2s; }
.btn:hover:not(:disabled) { border-color: #0f1f3d; }
.btn:disabled { opacity: .4; cursor: not-allowed; }
.btn-sm { padding: 5px 11px; font-size: 12px; }
</style>