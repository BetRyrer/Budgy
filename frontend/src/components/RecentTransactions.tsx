import type { Transaction } from '../types';

interface RecentTransactionsProps {
  transactions: Transaction[];
}

const amountFormatter = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });
const dateFormatter = new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' });

export default function RecentTransactions({ transactions }: RecentTransactionsProps) {
  if (transactions.length === 0) {
    return <p className="text-sm text-slate-500 dark:text-slate-400">Aucune transaction pour le moment.</p>;
  }

  return (
    <ul className="divide-y divide-slate-100 dark:divide-slate-700">
      {transactions.map((t) => (
        <li key={t.id} className="flex items-center justify-between py-3">
          <div className="flex items-center gap-3 min-w-0">
            {t.category && (
              <span
                className="h-2.5 w-2.5 rounded-full shrink-0"
                style={{ backgroundColor: t.category.color }}
              />
            )}
            <div className="min-w-0">
              <p className="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">{t.label}</p>
              <p className="text-xs text-slate-400 dark:text-slate-500">
                {dateFormatter.format(new Date(t.date))}
              </p>
            </div>
          </div>
          <span
            className={`text-sm font-medium whitespace-nowrap ${
              t.type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'
            }`}
          >
            {t.type === 'income' ? '+' : '-'}
            {amountFormatter.format(t.amount)}
          </span>
        </li>
      ))}
    </ul>
  );
}
