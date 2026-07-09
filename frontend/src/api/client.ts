import axios from 'axios';
import type {
  Category,
  CategoryInput,
  Stats,
  Transaction,
  TransactionInput,
} from '../types';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? 'http://localhost:8080/api',
  headers: { 'Content-Type': 'application/json' },
});

export interface TransactionFilters {
  month?: string;
  category?: number;
  search?: string;
  sort?: 'date' | 'label' | 'amount';
  order?: 'asc' | 'desc';
}

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
