<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue'
import type { Client, ClientForm, Company, PersonType } from '@/types'
import * as clientsApi from '@/api/clients'
import * as companiesApi from '@/api/companies'

const list = ref<Client[]>([])
const companies = ref<Company[]>([])
const loading = ref(false)
const error = ref('')
const meta = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0 })
const formVisible = ref(false)
const editingId = ref<number | null>(null)
const formLoading = ref(false)
const formErrors = ref<Record<string, string[]>>({})

const form = reactive<ClientForm>({
  company_id: null,
  name: '',
  rfc: '',
  person_type: 'MORAL',
  email: '',
  phone: '',
  address: '',
})

const formTitle = computed(() => (editingId.value ? 'Editar cliente' : 'Nuevo cliente'))
const nameLabel = computed(() =>
  form.person_type === 'FISICA' ? 'Nombre completo' : 'Razón social'
)

async function loadCompanies() {
  try {
    const res = await companiesApi.fetchCompanies(1)
    companies.value = res.data
  } catch {
    companies.value = []
  }
}

async function loadClients(page = 1) {
  loading.value = true
  error.value = ''
  try {
    const res = await clientsApi.fetchClients(page)
    list.value = res.data
    meta.value = res.meta
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    error.value = err.response?.data?.message ?? 'Error al cargar clientes'
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingId.value = null
  Object.assign(form, {
    company_id: null,
    name: '',
    rfc: '',
    person_type: 'MORAL' as PersonType,
    email: '',
    phone: '',
    address: '',
  })
  formErrors.value = {}
  formVisible.value = true
  loadCompanies()
}

function openEdit(client: Client) {
  editingId.value = client.id
  Object.assign(form, {
    company_id: client.company_id ?? null,
    name: client.name,
    rfc: client.rfc ?? '',
    person_type: client.person_type,
    email: client.email ?? '',
    phone: client.phone ?? '',
    address: client.address ?? '',
  })
  formErrors.value = {}
  formVisible.value = true
  loadCompanies()
}

function closeForm() {
  formVisible.value = false
  editingId.value = null
}

async function submitForm() {
  formLoading.value = true
  formErrors.value = {}
  try {
    const payload = {
      company_id: form.company_id || undefined,
      name: form.name.trim(),
      rfc: form.rfc.trim().toUpperCase() || undefined,
      person_type: form.person_type,
      email: form.email.trim() || undefined,
      phone: form.phone.trim() || undefined,
      address: form.address.trim() || undefined,
    }
    if (editingId.value) {
      await clientsApi.updateClient(editingId.value, payload)
    } else {
      await clientsApi.createClient(payload as ClientForm)
    }
    closeForm()
    await loadClients(meta.value.current_page)
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }
    formErrors.value = err.response?.data?.errors ?? {}
    if (err.response?.data?.message && !err.response?.data?.errors) {
      formErrors.value._ = [err.response.data.message]
    }
  } finally {
    formLoading.value = false
  }
}

async function confirmDelete(client: Client) {
  if (!window.confirm(`¿Eliminar "${client.name}"?`)) return
  try {
    await clientsApi.deleteClient(client.id)
    await loadClients(meta.value.current_page)
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    error.value = err.response?.data?.message ?? 'Error al eliminar'
  }
}

function getFirstError(errors: Record<string, string[]>) {
  return Object.values(errors).flat().find(Boolean) ?? ''
}

function companyName(client: Client): string {
  if (client.company) return client.company.name
  return client.company_id ? `#${client.company_id}` : '—'
}

onMounted(() => loadClients())
</script>

<template>
  <div class="clients-page">
    <div class="page-header">
      <h1>Clientes</h1>
      <button type="button" class="btn btn-primary" @click="openCreate">Nuevo cliente</button>
    </div>

    <p v-if="error" class="error-banner">{{ error }}</p>

    <div v-if="loading" class="loading">Cargando…</div>
    <template v-else>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Nombre / Razón social</th>
              <th>RFC</th>
              <th>Tipo</th>
              <th>Empresa</th>
              <th>Email</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in list" :key="c.id">
              <td>{{ c.name }}</td>
              <td><code>{{ c.rfc ?? '—' }}</code></td>
              <td>{{ c.person_type === 'FISICA' ? 'Persona física' : 'Persona moral' }}</td>
              <td>{{ companyName(c) }}</td>
              <td>{{ c.email ?? '—' }}</td>
              <td class="actions">
                <button type="button" class="btn btn-sm btn-edit" @click="openEdit(c)">Editar</button>
                <button type="button" class="btn btn-sm btn-danger" @click="confirmDelete(c)">
                  Eliminar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-if="list.length === 0" class="empty">No hay clientes. Crea uno con «Nuevo cliente».</p>

      <div v-if="meta.last_page > 1" class="pagination">
        <button
          type="button"
          class="btn btn-sm"
          :disabled="meta.current_page <= 1"
          @click="loadClients(meta.current_page - 1)"
        >
          Anterior
        </button>
        <span class="page-info">
          Página {{ meta.current_page }} de {{ meta.last_page }} ({{ meta.total }} en total)
        </span>
        <button
          type="button"
          class="btn btn-sm"
          :disabled="meta.current_page >= meta.last_page"
          @click="loadClients(meta.current_page + 1)"
        >
          Siguiente
        </button>
      </div>
    </template>

    <div v-if="formVisible" class="form-overlay" @click.self="closeForm">
      <div class="form-card">
        <h2>{{ formTitle }}</h2>
        <form @submit.prevent="submitForm">
          <p v-if="getFirstError(formErrors)" class="form-error">
            {{ getFirstError(formErrors) }}
          </p>

          <div class="field">
            <label>Tipo de persona</label>
            <select v-model="form.person_type" required>
              <option value="FISICA">Persona física</option>
              <option value="MORAL">Persona moral</option>
            </select>
          </div>

          <div class="field">
            <label>{{ nameLabel }}</label>
            <input v-model="form.name" type="text" required placeholder="Nombre o razón social" />
            <span v-if="formErrors.name?.length" class="field-error">{{ formErrors.name[0] }}</span>
          </div>

          <div class="field">
            <label>Empresa (opcional)</label>
            <select v-model="form.company_id">
              <option :value="null">— Sin empresa —</option>
              <option v-for="co in companies" :key="co.id" :value="co.id">
                {{ co.name }} ({{ co.rfc }})
              </option>
            </select>
          </div>

          <div class="field">
            <label>RFC</label>
            <input v-model="form.rfc" type="text" placeholder="Opcional" maxlength="13" />
            <span v-if="formErrors.rfc?.length" class="field-error">{{ formErrors.rfc[0] }}</span>
          </div>

          <div class="field">
            <label>Email</label>
            <input v-model="form.email" type="email" placeholder="Opcional" />
            <span v-if="formErrors.email?.length" class="field-error">{{ formErrors.email[0] }}</span>
          </div>

          <div class="field">
            <label>Teléfono</label>
            <input v-model="form.phone" type="text" placeholder="Opcional" />
          </div>

          <div class="field">
            <label>Domicilio</label>
            <input v-model="form.address" type="text" placeholder="Opcional" />
          </div>

          <div class="form-actions">
            <button type="button" class="btn" @click="closeForm">Cancelar</button>
            <button type="submit" class="btn btn-primary" :disabled="formLoading">
              {{ formLoading ? 'Guardando…' : 'Guardar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.clients-page {
  max-width: 1100px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.page-header h1 {
  margin: 0;
  font-size: 1.35rem;
  color: #1e293b;
}

.error-banner {
  margin: 0 0 1rem;
  padding: 0.6rem 0.75rem;
  background: #fef2f2;
  color: #b91c1c;
  border-radius: 8px;
  font-size: 0.9rem;
}

.loading {
  padding: 2rem;
  text-align: center;
  color: #64748b;
}

.table-wrap {
  overflow-x: auto;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.data-table th,
.data-table td {
  padding: 0.6rem 0.75rem;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
}

.data-table th {
  background: #f8fafc;
  font-weight: 600;
  color: #475569;
}

.data-table tbody tr:hover {
  background: #f8fafc;
}

.data-table code {
  font-size: 0.85em;
  background: #f1f5f9;
  padding: 0.15rem 0.4rem;
  border-radius: 4px;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.empty {
  margin: 1rem 0 0;
  color: #64748b;
  font-size: 0.95rem;
}

.pagination {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-top: 1rem;
}

.page-info {
  font-size: 0.875rem;
  color: #64748b;
}

.btn {
  padding: 0.5rem 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  background: #fff;
  font-size: 0.9rem;
  cursor: pointer;
}

.btn:hover:not(:disabled) {
  background: #f8fafc;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-sm {
  padding: 0.35rem 0.6rem;
  font-size: 0.85rem;
}

.btn-primary {
  background: #2563eb;
  color: #fff;
  border-color: #2563eb;
}

.btn-primary:hover:not(:disabled) {
  background: #1d4ed8;
}

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

.form-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  padding: 1rem;
}

.form-card {
  background: #fff;
  border-radius: 12px;
  padding: 1.5rem;
  width: 100%;
  max-width: 440px;
  max-height: 90vh;
  overflow-y: auto;
}

.form-card h2 {
  margin: 0 0 1rem;
  font-size: 1.2rem;
  color: #1e293b;
}

.form-error {
  margin: 0 0 1rem;
  padding: 0.5rem 0.75rem;
  background: #fef2f2;
  color: #b91c1c;
  border-radius: 6px;
  font-size: 0.875rem;
}

.form-card .field {
  margin-bottom: 1rem;
}

.form-card .field label {
  display: block;
  font-size: 0.85rem;
  font-weight: 500;
  color: #334155;
  margin-bottom: 0.35rem;
}

.form-card .field input,
.form-card .field select {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 1rem;
}

.form-card .field input:focus,
.form-card .field select:focus {
  outline: none;
  border-color: #2563eb;
}

.field-error {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.8rem;
  color: #b91c1c;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 1.25rem;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}
</style>
