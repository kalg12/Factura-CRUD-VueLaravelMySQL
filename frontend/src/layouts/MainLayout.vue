<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

async function logout() {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="main-layout">
    <header class="header">
      <div class="brand">
        <router-link :to="{ name: 'home' }">Facturación</router-link>
      </div>
      <nav class="nav">
        <router-link :to="{ name: 'companies' }">Empresas</router-link>
        <router-link :to="{ name: 'clients' }">Clientes</router-link>
        <router-link :to="{ name: 'invoices' }">Facturas</router-link>
      </nav>
      <div class="user">
        <span class="user-name">
          <span class="user-label">Usuario</span>
          {{ auth.user?.name ?? auth.user?.email }}
        </span>
        <button type="button" class="btn-logout" @click="logout">Cerrar sesión</button>
      </div>
    </header>
    <main class="main">
      <router-view />
    </main>
  </div>
</template>

<style scoped>
.main-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: #f8fafc;
}

.header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding: 0.75rem 1.5rem;
  background: #fff;
  border-bottom: 1px solid #e2e8f0;
  flex-wrap: wrap;
}

.brand a {
  font-weight: 700;
  font-size: 1.15rem;
  color: #1e293b;
  text-decoration: none;
}

.brand a:hover {
  color: #2563eb;
}

.nav {
  display: flex;
  gap: 0.5rem;
}

.nav a {
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  color: #475569;
  text-decoration: none;
  font-weight: 500;
  font-size: 0.9rem;
}

.nav a:hover {
  background: #f1f5f9;
  color: #1e293b;
}

.nav a.router-link-active {
  background: #eff6ff;
  color: #2563eb;
}

.user {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-name {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  font-weight: 600;
  background: #eff6ff;
  color: #1d4ed8;
  padding: 0.45rem 0.85rem;
  border-radius: 8px;
}

.user-label {
  font-weight: 500;
  color: #64748b;
}

.btn-logout {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #fff;
  background: #dc2626;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-logout:hover {
  background: #b91c1c;
}

.main {
  flex: 1;
  padding: 1.5rem;
}
</style>
