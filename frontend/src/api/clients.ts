import { api } from '@/api/client'
import type { Client, ClientForm, PaginatedData } from '@/types'

function unwrap<T>(body: { data?: T } | T): T {
  return (body && typeof body === 'object' && 'data' in body && body.data !== undefined)
    ? body.data
    : (body as T)
}

export async function fetchClients(page = 1): Promise<PaginatedData<Client>> {
  const { data } = await api.get<PaginatedData<Client>>('/clients', { params: { page } })
  return data
}

export async function fetchClient(id: number): Promise<Client> {
  const { data } = await api.get(`/clients/${id}`)
  return unwrap<Client>(data)
}

export async function createClient(payload: ClientForm): Promise<Client> {
  const { data } = await api.post('/clients', {
    ...payload,
    company_id: payload.company_id || undefined,
  })
  return unwrap<Client>(data)
}

export async function updateClient(id: number, payload: Partial<ClientForm>): Promise<Client> {
  const { data } = await api.put(`/clients/${id}`, {
    ...payload,
    company_id: payload.company_id ?? undefined,
  })
  return unwrap<Client>(data)
}

export async function deleteClient(id: number): Promise<void> {
  await api.delete(`/clients/${id}`)
}
