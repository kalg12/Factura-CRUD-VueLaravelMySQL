export interface User {
  id: number
  name: string
  email: string
}

export interface AuthResponse {
  message: string
  token: string
  token_type: string
  user: User
}

export type PersonType = 'FISICA' | 'MORAL'

export interface Company {
  id: number
  name: string
  rfc: string
  person_type: PersonType
  email: string | null
  phone: string | null
  address: string | null
  created_at: string
  updated_at: string
}

export interface CompanyForm {
  name: string
  rfc: string
  person_type: PersonType
  email: string
  phone: string
  address: string
}

export interface PaginatedData<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  links: { first: string; last: string; prev: string | null; next: string | null }
}

export interface Client {
  id: number
  company_id: number | null
  company?: Company | null
  name: string
  rfc: string | null
  person_type: PersonType
  email: string | null
  phone: string | null
  address: string | null
  created_at: string
  updated_at: string
}

export interface ClientForm {
  company_id: number | null
  name: string
  rfc: string
  person_type: PersonType
  email: string
  phone: string
  address: string
}

export interface InvoiceItem {
  id?: number
  description: string
  quantity: number
  unit_price: number
  total: number
}

export interface Invoice {
  id: number
  company_id: number
  client_id: number
  date: string
  status: string
  subtotal: number
  tax: number
  total: number
  currency: string
  items?: InvoiceItem[]
  company?: Company
  client?: Client
  created_at: string
  updated_at: string
}

export interface InvoiceForm {
  company_id: number
  client_id: number
  date: string
  status: string
  currency: string
  items: { description: string; quantity: number; unit_price: number }[]
}
