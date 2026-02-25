import { api } from '@/api/client'
import type { Company, CompanyForm, PaginatedData } from '@/types'

export async function fetchCompanies(page = 1): Promise<PaginatedData<Company>> {
  const { data } = await api.get<PaginatedData<Company>>('/companies', {
    params: { page },
  })
  return data
}

function unwrap<T>(body: { data?: T } | T): T {
  return (body && typeof body === 'object' && 'data' in body && body.data !== undefined)
    ? body.data
    : (body as T)
}

export async function fetchCompany(id: number): Promise<Company> {
  const { data } = await api.get(`/companies/${id}`)
  return unwrap<Company>(data)
}

export async function createCompany(payload: CompanyForm): Promise<Company> {
  const { data } = await api.post(`/companies`, payload)
  return unwrap<Company>(data)
}

export async function updateCompany(id: number, payload: Partial<CompanyForm>): Promise<Company> {
  const { data } = await api.put(`/companies/${id}`, payload)
  return unwrap<Company>(data)
}

export async function deleteCompany(id: number): Promise<void> {
  await api.delete(`/companies/${id}`)
}
