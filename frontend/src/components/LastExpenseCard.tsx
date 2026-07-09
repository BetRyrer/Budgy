import type { Transaction } from '../types';

interface LastExpenseCardProps {
  expense: Transaction | null;
}

const amountFormatter = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });
const dateFormatter = new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' });

export default function LastExpenseCard({ expense }: LastExpenseCardProps) {
  return (
    <div className="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm">
      <p className="text-sm text-slate-500 dark:text-slate-400 font-medium">Dernière dépense</p>
      {expense ? (
        <>
          <p className="mt-2 text-2xl font-semibold text-rose-600 dark:text-rose-400">
            -{amountFormatter.format(expense.amount)}
          </p>
          <p className="mt-1 text-sm text-slate-600 dark:text-slate-300 truncate">{expense.label}</p>
          <p className="text-xs text-slate-400 dark:text-slate-500">
            {dateFormatter.format(new Date(expense.date))}
          </p>
        </>
      ) : (
        <p className="mt-2 text-sm text-slate-400 dark:text-slate-500">Aucune dépense enregistrée.</p>
      )}
    </div>
  );
}
