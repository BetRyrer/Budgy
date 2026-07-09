import { useEffect, useState } from 'react';
import { statsApi } from '../api/client';
import type { Stats } from '../types';
import SummaryCard from '../components/SummaryCard';
import LastExpenseCard from '../components/LastExpenseCard';
import ExpensePieChart from '../components/ExpensePieChart';
import MonthlyChart from '../components/MonthlyChart';
import RecentTransactions from '../components/RecentTransactions';

export default function Dashboard() {
  const [stats, setStats] = useState<Stats | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let cancelled = false;

    statsApi
      .get()
      .then((data) => {
        if (!cancelled) setStats(data);
      })
      .catch(() => {
        if (!cancelled) setError('Impossible de charger les statistiques.');
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, []);

  if (loading) {
    return <p className="text-slate-500 dark:text-slate-400">Chargement du tableau de bord...</p>;
  }

  if (error || !stats) {
    return <p className="text-rose-600 dark:text-rose-400">{error ?? 'Erreur inconnue.'}</p>;
  }

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <SummaryCard label="Solde total" amount={stats.balance} tone="neutral" />
        <SummaryCard label="Revenus du mois" amount={stats.currentMonth.income} tone="positive" />
        <SummaryCard label="Dépenses du mois" amount={stats.currentMonth.expense} tone="negative" />
        <LastExpenseCard expense={stats.lastExpense} />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div className="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm">
          <h3 className="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">
            Répartition des dépenses par catégorie
          </h3>
          <ExpensePieChart data={stats.expensesByCategory} />
        </div>
        <div className="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm">
          <h3 className="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">Évolution mensuelle</h3>
          <MonthlyChart data={stats.monthlyEvolution} />
        </div>
      </div>

      <div className="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm">
        <h3 className="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Transactions récentes</h3>
        <RecentTransactions transactions={stats.recentTransactions} />
      </div>
    </div>
  );
}
