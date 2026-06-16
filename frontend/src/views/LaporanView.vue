<template>
  <div>
    <div class="page-heading">
      <BarChart3 class="heading-icon" />
      <h1>Laporan & Statistik</h1>
    </div>

    <div class="grid grid-4 mb-6">
      <div class="stat-card">
        <div class="stat-icon blue"><Briefcase class="stat-svg" /></div>
        <div>
          <div class="stat-value">{{ data.peminjaman.length }}</div>
          <div class="stat-label">Total Peminjaman</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><CircleCheck class="stat-svg" /></div>
        <div>
          <div class="stat-value">{{ disetujui }}</div>
          <div class="stat-label">Disetujui</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red"><CircleX class="stat-svg" /></div>
        <div>
          <div class="stat-value">{{ ditolak }}</div>
          <div class="stat-label">Ditolak</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon amber"><Wrench class="stat-svg" /></div>
        <div>
          <div class="stat-value">{{ data.kerusakan.length }}</div>
          <div class="stat-label">Laporan Kerusakan</div>
        </div>
      </div>
    </div>

    <div class="grid grid-2">
      <!-- Statistik inventaris per lab -->
      <div class="card">
        <div class="card-title mb-4">
          <FlaskConical class="icon-sm" /> Statistik Inventaris per Lab
        </div>
        <div v-for="lab in labList" :key="lab" class="lab-row">
          <div class="lab-info">
            <Building2 class="lab-icon" />
            <div>
              <div class="lab-name">{{ lab }}</div>
              <div class="lab-meta">
                <Layers class="icon-xs" /> {{ totalPerLab(lab) }} aset total
              </div>
            </div>
          </div>
          <div class="lab-badges">
            <span v-if="rusakPerLab(lab)" class="badge badge-red">
              <CircleX class="badge-icon" /> {{ rusakPerLab(lab) }} rusak
            </span>
            <span class="badge badge-green">
              <CircleCheck class="badge-icon" /> {{ totalPerLab(lab) - rusakPerLab(lab) }} baik
            </span>
          </div>
        </div>
        <div v-if="!labList.length" class="empty-state">
          <PackageX class="empty-icon" />
          <span>Belum ada data inventaris.</span>
        </div>
      </div>

      <!-- Statistik pengguna -->
      <div class="card">
        <div class="card-title mb-4">
          <Users class="icon-sm" /> Statistik Pengguna
        </div>
        <div v-for="(label, role) in roleMap" :key="role" class="role-row">
          <div class="role-label-wrap">
            <component :is="roleIcons[role]" class="role-icon" />
            <span class="role-label">{{ label }}</span>
          </div>
          <span class="badge badge-blue">
            <User class="badge-icon" /> {{ countByRole(role) }} orang
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useDataStore } from '@/stores/data'
import {
  BarChart3, Briefcase, CircleCheck, CircleX, Wrench,
  FlaskConical, Building2, Layers, PackageX,
  Users, User, GraduationCap, UserCheck, BookOpen, ShieldCheck
} from 'lucide-vue-next'

const data = useDataStore()

onMounted(() => {
  data.fetchPeminjaman?.().catch(() => {})
  data.fetchKerusakan?.().catch(() => {})
  data.fetchInventaris?.().catch(() => {})
  data.fetchUsers?.().catch(() => {})
})

const disetujui = computed(() => data.peminjaman.filter(p => p.status === 'Disetujui').length)
const ditolak   = computed(() => data.peminjaman.filter(p => p.status === 'Ditolak').length)

const labList = computed(() => [...new Set(data.inventaris.map(i => i.lab))].filter(Boolean))

function totalPerLab(lab) { return data.inventaris.filter(i => i.lab === lab).length }
function rusakPerLab(lab) { return data.inventaris.filter(i => i.lab === lab && i.kondisi === 'Rusak').length }

const roleMap = {
  mahasiswa: 'Mahasiswa',
  asprak:    'Asisten Praktikum',
  aslab:     'Asisten Lab',
  dosen:     'Dosen',
  admin:     'Admin',
}

const roleIcons = {
  mahasiswa: GraduationCap,
  asprak:    UserCheck,
  aslab:     FlaskConical,
  dosen:     BookOpen,
  admin:     ShieldCheck,
}

function countByRole(role) { return data.users?.filter(u => u.role === role).length || 0 }
</script>

<style scoped>
/* Heading */
.page-heading { display: flex; align-items: center; gap: 10px; margin-bottom: 24px; }
.page-heading h1 { font-size: 22px; font-weight: 700; color: #0f1f3d; }
.heading-icon { width: 26px; height: 26px; color: #2563eb; flex-shrink: 0; }

/* Grid */
.grid { display: grid; gap: 20px; }
.grid-2 { grid-template-columns: repeat(2, 1fr); }
.grid-4 { grid-template-columns: repeat(4, 1fr); }
.mb-6   { margin-bottom: 24px; }

/* Stat cards */
.stat-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 16px rgba(15,31,61,.08); display: flex; align-items: flex-start; gap: 16px; }
.stat-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-icon.blue  { background: #dbeafe; color: #2563eb; }
.stat-icon.green { background: #d1fae5; color: #059669; }
.stat-icon.amber { background: #fef3c7; color: #d97706; }
.stat-icon.red   { background: #fee2e2; color: #dc2626; }
.stat-svg   { width: 22px; height: 22px; }
.stat-value { font-size: 28px; font-weight: 700; color: #0f1f3d; line-height: 1; }
.stat-label { font-size: 12px; color: #64748b; margin-top: 4px; }

/* Card */
.card { background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 16px rgba(15,31,61,.08); }
.card-title { font-size: 15px; font-weight: 700; color: #0f1f3d; display: flex; align-items: center; gap: 7px; }
.mb-4 { margin-bottom: 16px; }

/* Lab rows */
.lab-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
.lab-info { display: flex; align-items: center; gap: 10px; }
.lab-icon { width: 16px; height: 16px; color: #2563eb; flex-shrink: 0; }
.lab-name { font-size: 13px; font-weight: 600; color: #0f1f3d; }
.lab-meta { display: flex; align-items: center; gap: 4px; font-size: 11px; color: #64748b; margin-top: 2px; }
.lab-badges { display: flex; gap: 6px; }

/* Role rows */
.role-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
.role-label-wrap { display: flex; align-items: center; gap: 8px; }
.role-icon  { width: 15px; height: 15px; color: #64748b; }
.role-label { font-size: 13px; font-weight: 600; color: #0f1f3d; }

/* Badge */
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-icon { width: 11px; height: 11px; }
.badge-green { background: #d1fae5; color: #065f46; }
.badge-amber { background: #fef3c7; color: #92400e; }
.badge-red   { background: #fee2e2; color: #991b1b; }
.badge-blue  { background: #dbeafe; color: #1e40af; }

/* Empty state */
.empty-state { display: flex; flex-direction: column; align-items: center; gap: 8px; text-align: center; color: #94a3b8; padding: 20px; }
.empty-icon { width: 32px; height: 32px; color: #cbd5e1; }

/* Icon sizes */
.icon-xs { width: 13px; height: 13px; }
.icon-sm { width: 16px; height: 16px; }
</style>