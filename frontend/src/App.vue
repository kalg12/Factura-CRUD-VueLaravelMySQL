<script setup lang="ts"></script>

<template>
  <router-view />
</template>

<style>
*,
*::before,
*::after {
  box-sizing: border-box;
}

body {
  margin: 0;
  font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
  -webkit-font-smoothing: antialiased;
}

/* Botones de acción en tablas: Editar (azul) y Eliminar (rojo) */
.btn-edit {
  background: #3b82f6;
  color: #fff;
  border-color: #3b82f6;
}
.btn-edit:hover:not(:disabled) {
  background: #2563eb;
  border-color: #2563eb;
  color: #fff;
}

.btn-danger {
  background: #ef4444;
  color: #fff;
  border-color: #ef4444;
}
.btn-danger:hover:not(:disabled) {
  background: #dc2626;
  border-color: #dc2626;
  color: #fff;
}
</style>
