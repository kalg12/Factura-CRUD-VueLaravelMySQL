<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue'
import type { Invoice, InvoiceForm, InvoiceItem, Company, Client } from '@/types'
import * as invoicesApi from '@/api/invoices'
import * as companiesApi from '@/api/companies'
import * as clientsApi from '@/api/clients'

const list = ref<Invoice[]>([])
const companies = ref<Company[]>([])
const clients = ref<Client[]>([])
const selectedIds = ref<Set<number>>(new Set())
const loading = ref(false)
const error = ref('')
const meta = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0 })
const formVisible = ref(false)
const editingId = ref<number | null>(null)
const formLoading = ref(false)
const formErrors = ref<Record<string, string[]>>({})
const exportLoading = ref<'pdf' | 'csv' | null>(null)

const form = reactive<InvoiceForm>({
  company_id: 0,
  client_id: 0,
  date: new Date().toISOString().slice(0, 10),
  status: 'DRAFT',
  currency: 'MXN',
  items: [{ description: '', quantity: 1, unit_price: 0 }],
})

const formTitle = computed(() => (editingId.value ? 'Editar factura' : 'Nueva factura'))
const hasSelection = computed(() => selectedIds.value.size > 0)
const selectionCount = computed(() => selectedIds.value.size)

const subtotal = computed(() =>
  form.items.reduce((sum, row) => sum + row.quantity * row.unit_price, 0)
)
const tax = computed(() => subtotal.value * 0.16)
const total = computed(() => subtotal.value + tax.value)

function lineTotal(item: { quantity: number; unit_price: number }) {
  return item.quantity * item.unit_price
}

async function loadCompanies() {
  try {
    const res = await companiesApi.fetchCompanies(1)
    companies.value = res.data
  } catch {
    companies.value = []
  }
}

async function loadClients() {
  try {
    const res = await clientsApi.fetchClients(1)
    clients.value = res.data
  } catch {
    clients.value = []
  }
}

async function loadInvoices(page = 1) {
  loading.value = true
  error.value = ''
  try {
    const res = await invoicesApi.fetchInvoices(page)
    list.value = res.data
    meta.value = res.meta
    selectedIds.value = new Set()
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    error.value = err.response?.data?.message ?? 'Error al cargar facturas'
  } finally {
    loading.value = false
  }
}

function toggleSelect(id: number) {
  const next = new Set(selectedIds.value)
  if (next.has(id)) next.delete(id)
  else next.add(id)
  selectedIds.value = next
}

function toggleSelectAll() {
  if (selectedIds.value.size === list.value.length) {
    selectedIds.value = new Set()
  } else {
    selectedIds.value = new Set(list.value.map((i) => i.id))
  }
}

function openCreate() {
  editingId.value = null
  form.company_id = companies.value[0]?.id ?? 0
  form.client_id = clients.value[0]?.id ?? 0
  form.date = new Date().toISOString().slice(0, 10)
  form.status = 'DRAFT'
  form.currency = 'MXN'
  form.items = [{ description: '', quantity: 1, unit_price: 0 }]
  formErrors.value = {}
  formVisible.value = true
  loadCompanies()
  loadClients()
}

async function openEdit(inv: Invoice) {
  editingId.value = inv.id
  try {
    const full = await invoicesApi.fetchInvoice(inv.id)
    form.company_id = full.company_id
    form.client_id = full.client_id
    form.date = full.date
    form.status = full.status
    form.currency = full.currency
    form.items =
      (full.items?.length ?? 0) > 0
        ? full.items!.map((it: InvoiceItem) => ({
            description: it.description,
            quantity: it.quantity,
            unit_price: it.unit_price,
          }))
        : [{ description: '', quantity: 1, unit_price: 0 }]
  } catch {
    form.items = [{ description: '', quantity: 1, unit_price: 0 }]
  }
  formErrors.value = {}
  formVisible.value = true
  loadCompanies()
  loadClients()
}

function closeForm() {
  formVisible.value = false
  editingId.value = null
}

function addItem() {
  form.items.push({ description: '', quantity: 1, unit_price: 0 })
}

function removeItem(index: number) {
  if (form.items.length <= 1) return
  form.items.splice(index, 1)
}

async function submitForm() {
  formLoading.value = true
  formErrors.value = {}
  if (!form.company_id || !form.client_id) {
    formErrors.value._ = ['Selecciona empresa y cliente.']
    formLoading.value = false
    return
  }
  const validItems = form.items.filter(
    (row) => row.description.trim() && row.quantity > 0 && row.unit_price >= 0
  )
  if (validItems.length === 0) {
    formErrors.value.items = ['Añade al menos una partida con descripción, cantidad y precio.']
    formLoading.value = false
    return
  }
  try {
    const payload: InvoiceForm = {
      company_id: form.company_id,
      client_id: form.client_id,
      date: form.date,
      status: form.status,
      currency: form.currency,
      items: validItems.map((row) => ({
        description: row.description.trim(),
        quantity: Number(row.quantity),
        unit_price: Number(row.unit_price),
      })),
    }
    if (editingId.value) {
      await invoicesApi.updateInvoice(editingId.value, payload)
    } else {
      await invoicesApi.createInvoice(payload)
    }
    closeForm()
    await loadInvoices(meta.value.current_page)
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

async function confirmDelete(inv: Invoice) {
  if (!window.confirm(`¿Eliminar factura #${inv.id}?`)) return
  try {
    await invoicesApi.deleteInvoice(inv.id)
    await loadInvoices(meta.value.current_page)
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    error.value = err.response?.data?.message ?? 'Error al eliminar'
  }
}

async function doExport(format: 'pdf' | 'csv') {
  const ids = Array.from(selectedIds.value)
  if (ids.length === 0) return
  exportLoading.value = format
  try {
    await invoicesApi.downloadExport(ids, format)
  } catch (e: unknown) {
    error.value = (e as Error).message ?? 'Error al descargar'
  } finally {
    exportLoading.value = null
  }
}

function getFirstError(errors: Record<string, string[]>) {
  return Object.values(errors).flat().find(Boolean) ?? ''
}

function companyName(inv: Invoice): string {
  return inv.company?.name ?? `#${inv.company_id}`
}

function clientName(inv: Invoice): string {
  return inv.client?.name ?? `#${inv.client_id}`
}

onMounted(() => loadInvoices())
</script>

<template>
  <div class="invoices-page">
    <div class="page-header">
      <h1>Facturas</h1>
      <div class="header-actions">
        <button
          v-if="hasSelection"
          type="button"
          class="btn btn-sm"
          :disabled="exportLoading !== null"
          @click="doExport('pdf')"
        >
          {{ exportLoading === 'pdf' ? 'Descargando…' : 'Descargar PDF' }} ({{ selectionCount }})
        </button>
        <button
          v-if="hasSelection"
          type="button"
          class="btn btn-sm"
          :disabled="exportLoading !== null"
          @click="doExport('csv')"
        >
          {{ exportLoading === 'csv' ? 'Descargando…' : 'Descargar CSV' }} ({{ selectionCount }})
        </button>
        <button type="button" class="btn btn-primary" @click="openCreate">Nueva factura</button>
      </div>
    </div>

    <p v-if="error" class="error-banner">{{ error }}</p>

    <div v-if="loading" class="loading">Cargando…</div>
    <template v-else>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width: 2.5rem">
                <input
                  type="checkbox"
                  :checked="list.length > 0 && selectedIds.size === list.length"
                  :indeterminate="selectedIds.size > 0 && selectedIds.size < list.length"
                  @change="toggleSelectAll"
                />
              </th>
              <th>#</th>
              <th>Fecha</th>
              <th>Empresa</th>
              <th>Cliente</th>
              <th>Total</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="inv in list" :key="inv.id">
              <td>
                <input
                  type="checkbox"
                  :checked="selectedIds.has(inv.id)"
                  @change="toggleSelect(inv.id)"
                />
              </td>
              <td>{{ inv.id }}</td>
              <td>{{ inv.date }}</td>
              <td>{{ companyName(inv) }}</td>
              <td>{{ clientName(inv) }}</td>
              <td>{{ inv.currency }} {{ inv.total.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}</td>
              <td>{{ inv.status }}</td>
              <td class="actions">
                <button type="button" class="btn btn-sm" @click="openEdit(inv)">Editar</button>
                <button type="button" class="btn btn-sm btn-danger" @click="confirmDelete(inv)">
                  Eliminar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-if="list.length === 0" class="empty">No hay facturas. Crea una con «Nueva factura».</p>

      <div v-if="meta.last_page > 1" class="pagination">
        <button
          type="button"
          class="btn btn-sm"
          :disabled="meta.current_page <= 1"
          @click="loadInvoices(meta.current_page - 1)"
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
          @click="loadInvoices(meta.current_page + 1)"
        >
          Siguiente
        </button>
      </div>
    </template>

    <div v-if="formVisible" class="form-overlay" @click.self="closeForm">
      <div class="form-card form-card-wide">
        <h2>{{ formTitle }}</h2>
        <form @submit.prevent="submitForm">
          <p v-if="getFirstError(formErrors)" class="form-error">
            {{ getFirstError(formErrors) }}
          </p>

          <div class="form-row">
            <div class="field">
              <label>Empresa</label>
              <select v-model.number="form.company_id" required>
                <option :value="0">— Seleccione —</option>
                <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div class="field">
              <label>Cliente</label>
              <select v-model.number="form.client_id" required>
                <option :value="0">— Seleccione —</option>
                <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
          </div>

          <div class="form-row form-row-3">
            <div class="field">
              <label>Fecha</label>
              <input v-model="form.date" type="date" required />
            </div>
            <div class="field">
              <label>Estado</label>
              <select v-model="form.status">
                <option value="DRAFT">Borrador</option>
                <option value="SENT">Enviada</option>
                <option value="PAID">Pagada</option>
                <option value="CANCELLED">Cancelada</option>
              </select>
            </div>
            <div class="field">
              <label>Moneda</label>
              <input v-model="form.currency" type="text" maxlength="3" />
            </div>
          </div>

          <div class="items-section">
            <div class="items-header">
              <label>Partidas</label>
              <button type="button" class="btn btn-sm" @click="addItem">+ Añadir línea</button>
            </div>
            <div class="items-table">
              <table>
                <thead>
                  <tr>
                    <th>Descripción</th>
                    <th style="width: 6rem">Cantidad</th>
                    <th style="width: 7rem">P. unit.</th>
                    <th style="width: 6rem">Total</th>
                    <th style="width: 2.5rem"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(row, idx) in form.items" :key="idx">
                    <td>
                      <input v-model="row.description" type="text" placeholder="Descripción" />
                    </td>
                    <td>
                      <input v-model.number="row.quantity" type="number" min="0" step="0.01" />
                    </td>
                    <td>
                      <input v-model.number="row.unit_price" type="number" min="0" step="0.01" />
                    </td>
                    <td>{{ lineTotal(row).toFixed(2) }}</td>
                    <td>
                      <button
                        type="button"
                        class="btn btn-sm btn-danger"
                        :disabled="form.items.length <= 1"
                        @click="removeItem(idx)"
                      >
                        ×
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p v-if="formErrors.items?.length" class="field-error">{{ formErrors.items[0] }}</p>
            <div class="totals-row">
              <span>Subtotal:</span>
              <strong>{{ subtotal.toFixed(2) }}</strong>
            </div>
            <div class="totals-row">
              <span>IVA (16%):</span>
              <strong>{{ tax.toFixed(2) }}</strong>
            </div>
            <div class="totals-row total">
              <span>Total:</span>
              <strong>{{ form.currency }} {{ total.toFixed(2) }}</strong>
            </div>
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
.invoices-page {
  max-width: 1200px;
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

.header-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
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

.btn-danger:hover:not(:disabled) {
  background: #fef2f2;
  color: #b91c1c;
  border-color: #fecaca;
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
  overflow-y: auto;
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

.form-card-wide {
  max-width: 720px;
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

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-row .field:only-child {
  grid-column: 1 / -1;
}

.form-row-3 {
  grid-template-columns: 1fr 1fr 1fr;
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

.items-section {
  margin: 1rem 0;
  padding: 1rem 0;
  border-top: 1px solid #e2e8f0;
}

.items-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.items-header label {
  font-weight: 600;
  color: #334155;
}

.items-table {
  overflow-x: auto;
  margin-bottom: 0.5rem;
}

.items-table table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.items-table th,
.items-table td {
  padding: 0.4rem 0.5rem;
  border: 1px solid #e2e8f0;
  text-align: left;
}

.items-table th {
  background: #f8fafc;
}

.items-table input {
  width: 100%;
  padding: 0.35rem 0.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
}

.totals-row {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 0.25rem 0;
  font-size: 0.9rem;
}

.totals-row.total {
  font-size: 1rem;
  margin-top: 0.25rem;
  padding-top: 0.5rem;
  border-top: 1px solid #e2e8f0;
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
