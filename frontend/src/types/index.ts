export type TransactionType = 'income' | 'expense';

export interface Category {
  id: number;
  name: string;
  color: string;
}

export interface Transaction {
  id: number;
  label: string;
  amount: number;
  type: TransactionType;
  date: string; // format AAAA-MM-JJ
  category: Category | null;
}

export interface TransactionInput {
  label: string;
  amount: number;
  type: TransactionType;
  date: string;
  categoryId: number;
}

export interface CategoryInput {
  name: string;
  color: string;
}

export interface CategoryExpense {
  id: number;
  name: string;
  color: string;
  total: number;
}

export interface MonthlyEvolutionEntry {
  month: string; // AAAA-MM
  income: number;
  expense: number;
}

export interface Stats {
  totalIncome: number;
  totalExpense: number;
  balance: number;
  currentMonth: {
    month: string;
    income: number;
    expense: number;
    balance: number;
  };
  expensesByCategory: CategoryExpense[];
  monthlyEvolution: MonthlyEvolutionEntry[];
  lastExpense: Transaction | null;
  recentTransactions: Transaction[];
}

export interface ApiError {
  error: string;
  details?: Record<string, string>;
}
