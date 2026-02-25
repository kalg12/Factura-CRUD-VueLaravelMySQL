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
