<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
</script>

<template>
  <div class="home">
    <h1>Bienvenido, {{ auth.user?.name ?? 'Usuario' }}</h1>
    <p class="lead">
      Usa el menú para acceder a <strong>Empresas</strong>, <strong>Clientes</strong> y
      <strong>Facturas</strong>.
    </p>
  </div>
</template>

<style scoped>
.home {
  padding: 1.5rem;
}

.home h1 {
  margin: 0 0 0.5rem;
  font-size: 1.5rem;
  color: #1e293b;
}

.lead {
  margin: 0;
  color: #64748b;
  font-size: 1rem;
}
</style>
