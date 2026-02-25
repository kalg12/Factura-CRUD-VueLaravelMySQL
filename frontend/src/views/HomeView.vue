<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
</script>

<template>
  <div class="home">
    <div class="home-card">
      <h1>Bienvenido, {{ auth.user?.name ?? 'Usuario' }}</h1>
      <p class="lead">
        Gestiona empresas, clientes y facturas. Selecciona una sección para comenzar.
      </p>
      <div class="quick-links">
        <router-link :to="{ name: 'companies' }" class="quick-link">
          <span class="quick-link-icon">🏢</span>
          <span class="quick-link-label">Empresas</span>
          <span class="quick-link-desc">Alta y edición de empresas</span>
        </router-link>
        <router-link :to="{ name: 'clients' }" class="quick-link">
          <span class="quick-link-icon">👤</span>
          <span class="quick-link-label">Clientes</span>
          <span class="quick-link-desc">Alta y edición de clientes</span>
        </router-link>
        <router-link :to="{ name: 'invoices' }" class="quick-link">
          <span class="quick-link-icon">📄</span>
          <span class="quick-link-label">Facturas</span>
          <span class="quick-link-desc">Crear facturas y exportar PDF/CSV</span>
        </router-link>
      </div>
    </div>
  </div>
</template>

<style scoped>
.home {
  max-width: 720px;
  margin: 0 auto;
  padding: 2rem 1.5rem;
  min-height: 60vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

.home-card {
  width: 100%;
  background: #fff;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
  border: 1px solid #e2e8f0;
}

.home-card h1 {
  margin: 0 0 0.5rem;
  font-size: 1.5rem;
  color: #1e293b;
}

.lead {
  margin: 0 0 1.75rem;
  color: #64748b;
  font-size: 1rem;
  line-height: 1.5;
}

.quick-links {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
}

.quick-link {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.35rem;
  padding: 1.25rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  text-align: left;
  transition: all 0.2s;
  text-decoration: none;
  color: inherit;
}

.quick-link:hover {
  background: #eff6ff;
  border-color: #3b82f6;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.quick-link-icon {
  font-size: 1.75rem;
  line-height: 1;
}

.quick-link-label {
  font-weight: 600;
  font-size: 1rem;
  color: #1e293b;
}

.quick-link-desc {
  font-size: 0.8rem;
  color: #64748b;
  line-height: 1.3;
}
</style>
