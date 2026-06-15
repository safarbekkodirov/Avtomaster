export interface AuthUser {
  id:        number
  email:     string
  roles:     string[]
  firstName: string | null
  lastName:  string | null
  avatar:    string | null
}

export interface AuthResponse {
  accessToken: string
  expiresIn:   number
  user:        AuthUser
}

export interface LoginPayload {
  email:    string
  password: string
}

export interface RegisterPayload {
  email:     string
  password:  string
  firstName: string
  lastName:  string
  role:      'client' | 'master'
}
