import axios from 'axios';
import type {
  AuthResponse,
  Category,
  CategoryInput,
  LoginInput,
  RegisterInput,
  Stats,
  Transaction,
  TransactionInput,
  User,
} from '../types';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? 'http://localhost:8080/api',
  headers: { 'Content-Type': 'application/json' },
});

// Injecte le token JWT courant sur chaque requête sortante.
let authToken: string | null = null;

export function setAuthToken(token: string | null): void {
  authToken = token;
}

api.interceptors.request.use((config) => {
  if (authToken) {
    config.headers.Authorization = `Bearer ${authToken}`;
  }
  return config;
});

// Prévient l'AuthContext qu'un token n'est plus valide (expiré/révoqué) pour déclencher une déconnexion.
let unauthorizedHandler: (() => void) | null = null;

export function setUnauthorizedHandler(handler: (() => void) | null): void {
  unauthorizedHandler = handler;
}

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401 && unauthorizedHandler) {
      unauthorizedHandler();
    }
    return Promise.reject(error);
  },
);

export interface TransactionFilters {
  month?: string;
  category?: number;
  search?: string;
  sort?: 'date' | 'label' | 'amount';
  order?: 'asc' | 'desc';
}

export const authApi = {
  login: (input: LoginInput) => api.post<AuthResponse>('/login', input).then((r) => r.data),
  register: (input: RegisterInput) =>
    api.post<AuthResponse>('/register', input).then((r) => r.data),
  me: () => api.get<User>('/me').then((r) => r.data),
};

export const categoriesApi = {
  list: () => api.get<Category[]>('/categories').then((r) => r.data),
  create: (input: CategoryInput) =>
    api.post<Category>('/categories', input).then((r) => r.data),
  update: (id: number, input: CategoryInput) =>
    api.put<Category>(`/categories/${id}`, input).then((r) => r.data),
  remove: (id: number) => api.delete(`/categories/${id}`),
};

export const transactionsApi = {
  list: (filters: TransactionFilters = {}) =>
    api
      .get<Transaction[]>('/transactions', { params: filters })
      .then((r) => r.data),
  create: (input: TransactionInput) =>
    api.post<Transaction>('/transactions', input).then((r) => r.data),
  update: (id: number, input: TransactionInput) =>
    api.put<Transaction>(`/transactions/${id}`, input).then((r) => r.data),
  remove: (id: number) => api.delete(`/transactions/${id}`),
};

export const statsApi = {
  get: () => api.get<Stats>('/stats').then((r) => r.data),
};

export default api;
