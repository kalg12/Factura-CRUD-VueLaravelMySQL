import { api } from '@/api/client'
import type { Invoice, InvoiceForm, PaginatedData } from '@/types'

function unwrap<T>(body: { data?: T } | T): T {
  return (body && typeof body === 'object' && 'data' in body && body.data !== undefined)
    ? body.data
    : (body as T)
}

export async function fetchInvoices(page = 1): Promise<PaginatedData<Invoice>> {
  const { data } = await api.get<PaginatedData<Invoice>>('/invoices', { params: { page } })
  return data
}

export async function fetchInvoice(id: number): Promise<Invoice> {
  const { data } = await api.get(`/invoices/${id}`)
  return unwrap<Invoice>(data)
}

export async function createInvoice(payload: InvoiceForm): Promise<Invoice> {
  const { data } = await api.post('/invoices', payload)
  return unwrap<Invoice>(data)
}

export async function updateInvoice(id: number, payload: Partial<InvoiceForm>): Promise<Invoice> {
  const { data } = await api.put(`/invoices/${id}`, payload)
  return unwrap<Invoice>(data)
}

export async function deleteInvoice(id: number): Promise<void> {
  await api.delete(`/invoices/${id}`)
}

/** Download selected invoices as PDF or CSV (uses api client for Bearer token). */
export async function downloadExport(ids: number[], format: 'pdf' | 'csv'): Promise<void> {
  if (ids.length === 0) return
  const params = ids.map((id) => `ids[]=${id}`).join('&')
  const path = format === 'pdf' ? `/invoices/export/pdf?${params}` : `/invoices/export/csv?${params}`
  const { data } = await api.get(path, { responseType: 'blob' })
  const ext = format === 'pdf' ? 'pdf' : 'csv'
  const name = `facturas-${Date.now()}.${ext}`
  const a = document.createElement('a')
  a.href = URL.createObjectURL(data as Blob)
  a.download = name
  a.click()
  URL.revokeObjectURL(a.href)
}
