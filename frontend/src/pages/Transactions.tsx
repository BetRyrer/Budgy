import { useEffect, useMemo, useState } from 'react';
import { categoriesApi, transactionsApi } from '../api/client';
import type { Category, Transaction, TransactionInput } from '../types';
import TransactionModal from '../components/TransactionModal';
import ConfirmDialog from '../components/ConfirmDialog';

const amountFormatter = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });
const dateFormatter = new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' });

type SortField = 'date' | 'label' | 'amount';
type SortOrder = 'asc' | 'desc';

function defaultOrderFor(field: SortField): SortOrder {
  return field === 'label' ? 'asc' : 'desc';
}

export default function Transactions() {
  const [transactions, setTransactions] = useState<Transaction[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [monthFilter, setMonthFilter] = useState('');
  const [categoryFilter, setCategoryFilter] = useState<number | ''>('');
  const [searchInput, setSearchInput] = useState('');
  const [search, setSearch] = useState('');
  const [sortField, setSortField] = useState<SortField>('date');
  const [sortOrder, setSortOrder] = useState<SortOrder>('desc');

  const [editingTransaction, setEditingTransaction] = useState<Transaction | null>(null);
  const [showModal, setShowModal] = useState(false);
  const [deleteTarget, setDeleteTarget] = useState<Transaction | null>(null);

  // Petit debounce pour éviter une requête API à chaque frappe dans le champ de recherche
  useEffect(() => {
    const timeout = setTimeout(() => setSearch(searchInput.trim()), 300);
    return () => clearTimeout(timeout);
  }, [searchInput]);

  const filters = useMemo(
    () => ({
      month: monthFilter || undefined,
      category: categoryFilter === '' ? undefined : categoryFilter,
      search: search || undefined,
      sort: sortField,
      order: sortOrder,
    }),
    [monthFilter, categoryFilter, search, sortField, sortOrder],
  );

  function toggleSort(field: SortField) {
    if (field === sortField) {
      setSortOrder((current) => (current === 'asc' ? 'desc' : 'asc'));
    } else {
      setSortField(field);
      setSortOrder(defaultOrderFor(field));
    }
  }

  function sortIndicator(field: SortField) {
    if (field !== sortField) return null;
    return <span className="ml-1">{sortOrder === 'asc' ? '▲' : '▼'}</span>;
  }

  async function loadTransactions() {
    setLoading(true);
    setError(null);
    try {
      const data = await transactionsApi.list(filters);
      setTransactions(data);
    } catch {
      setError('Impossible de charger les transactions.');
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    categoriesApi.list().then(setCategories).catch(() => setError('Impossible de charger les catégories.'));
  }, []);

  useEffect(() => {
    loadTransactions();
  }, [filters]);

  function openCreateModal() {
    setEditingTransaction(null);
    setShowModal(true);
  }

  function openEditModal(transaction: Transaction) {
    setEditingTransaction(transaction);
    setShowModal(true);
  }

  async function handleSave(input: TransactionInput) {
    if (editingTransaction) {
      await transactionsApi.update(editingTransaction.id, input);
    } else {
      await transactionsApi.create(input);
    }
    setShowModal(false);
    await loadTransactions();
  }

  async function handleDelete() {
    if (!deleteTarget) return;
    await transactionsApi.remove(deleteTarget.id);
    setDeleteTarget(null);
    await loadTransactions();
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-xl font-semibold text-slate-900 dark:text-slate-100">Transactions</h1>
        <button
          onClick={openCreateModal}
          className="px-4 py-2 text-sm font-medium rounded-md bg-indigo-600 text-white hover:bg-indigo-700"
        >
          + Nouvelle transaction
        </button>
      </div>

      <div className="flex flex-wrap gap-3">
        <input
          type="search"
          value={searchInput}
          onChange={(e) => setSearchInput(e.target.value)}
          placeholder="Rechercher un libellé..."
          className="rounded-md border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-56"
        />
        <input
          type="month"
          value={monthFilter}
          onChange={(e) => setMonthFilter(e.target.value)}
          className="rounded-md border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
        <select
          value={categoryFilter}
          onChange={(e) => setCategoryFilter(e.target.value === '' ? '' : Number(e.target.value))}
          className="rounded-md border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <option value="">Toutes les catégories</option>
          {categories.map((c) => (
            <option key={c.id} value={c.id}>
              {c.name}
            </option>
          ))}
        </select>
        {(monthFilter || categoryFilter !== '' || searchInput) && (
          <button
            onClick={() => {
              setMonthFilter('');
              setCategoryFilter('');
              setSearchInput('');
            }}
            className="px-3 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100"
          >
            Réinitialiser
          </button>
        )}
      </div>

      <div className="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        {loading ? (
          <p className="p-5 text-slate-500 dark:text-slate-400 text-sm">Chargement...</p>
        ) : error ? (
          <p className="p-5 text-rose-600 dark:text-rose-400 text-sm">{error}</p>
        ) : transactions.length === 0 ? (
          <p className="p-5 text-slate-500 dark:text-slate-400 text-sm">Aucune transaction pour ces critères.</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400 text-left">
                <tr>
                  <th className="px-4 py-3 font-medium">
                    <button
                      onClick={() => toggleSort('date')}
                      className="flex items-center hover:text-slate-900 dark:hover:text-slate-100"
                    >
                      Date
                      {sortIndicator('date')}
                    </button>
                  </th>
                  <th className="px-4 py-3 font-medium">
                    <button
                      onClick={() => toggleSort('label')}
                      className="flex items-center hover:text-slate-900 dark:hover:text-slate-100"
                    >
                      Libellé
                      {sortIndicator('label')}
                    </button>
                  </th>
                  <th className="px-4 py-3 font-medium">Catégorie</th>
                  <th className="px-4 py-3 font-medium text-right">
                    <button
                      onClick={() => toggleSort('amount')}
                      className="flex items-center justify-end w-full hover:text-slate-900 dark:hover:text-slate-100"
                    >
                      Montant
                      {sortIndicator('amount')}
                    </button>
                  </th>
                  <th className="px-4 py-3 font-medium text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-slate-700">
                {transactions.map((t) => (
                  <tr key={t.id} className="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                    <td className="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400">
                      {dateFormatter.format(new Date(t.date))}
                    </td>
                    <td className="px-4 py-3 text-slate-900 dark:text-slate-100">{t.label}</td>
                    <td className="px-4 py-3">
                      {t.category && (
                        <span
                          className="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium"
                          style={{ backgroundColor: `${t.category.color}20`, color: t.category.color }}
                        >
                          <span
                            className="h-1.5 w-1.5 rounded-full"
                            style={{ backgroundColor: t.category.color }}
                          />
                          {t.category.name}
                        </span>
                      )}
                    </td>
                    <td
                      className={`px-4 py-3 text-right font-medium whitespace-nowrap ${
                        t.type === 'income'
                          ? 'text-emerald-600 dark:text-emerald-400'
                          : 'text-rose-600 dark:text-rose-400'
                      }`}
                    >
                      {t.type === 'income' ? '+' : '-'}
                      {amountFormatter.format(t.amount)}
                    </td>
                    <td className="px-4 py-3 text-right whitespace-nowrap">
                      <button
                        onClick={() => openEditModal(t)}
                        className="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium mr-3"
                      >
                        Modifier
                      </button>
                      <button
                        onClick={() => setDeleteTarget(t)}
                        className="text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 font-medium"
                      >
                        Supprimer
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {showModal && (
        <TransactionModal
          categories={categories}
          transaction={editingTransaction}
          onSave={handleSave}
          onClose={() => setShowModal(false)}
        />
      )}

      {deleteTarget && (
        <ConfirmDialog
          title="Supprimer la transaction"
          message={`Voulez-vous vraiment supprimer "${deleteTarget.label}" ?`}
          onConfirm={handleDelete}
          onCancel={() => setDeleteTarget(null)}
        />
      )}
    </div>
  );
}
