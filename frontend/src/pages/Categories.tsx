import { useEffect, useState } from 'react';
import { categoriesApi } from '../api/client';
import type { Category, CategoryInput } from '../types';
import CategoryModal from '../components/CategoryModal';
import ConfirmDialog from '../components/ConfirmDialog';

export default function Categories() {
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [editingCategory, setEditingCategory] = useState<Category | null>(null);
  const [showModal, setShowModal] = useState(false);
  const [deleteTarget, setDeleteTarget] = useState<Category | null>(null);
  const [deleteError, setDeleteError] = useState<string | null>(null);

  async function loadCategories() {
    setLoading(true);
    setError(null);
    try {
      const data = await categoriesApi.list();
      setCategories(data);
    } catch {
      setError('Impossible de charger les catégories.');
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    loadCategories();
  }, []);

  function openCreateModal() {
    setEditingCategory(null);
    setShowModal(true);
  }

  function openEditModal(category: Category) {
    setEditingCategory(category);
    setShowModal(true);
  }

  async function handleSave(input: CategoryInput) {
    if (editingCategory) {
      await categoriesApi.update(editingCategory.id, input);
    } else {
      await categoriesApi.create(input);
    }
    setShowModal(false);
    await loadCategories();
  }

  async function handleDelete() {
    if (!deleteTarget) return;
    setDeleteError(null);
    try {
      await categoriesApi.remove(deleteTarget.id);
      setDeleteTarget(null);
      await loadCategories();
    } catch {
      setDeleteError('Cette catégorie est utilisée par des transactions et ne peut pas être supprimée.');
    }
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-xl font-semibold text-slate-900 dark:text-slate-100">Catégories</h1>
        <button
          onClick={openCreateModal}
          className="px-4 py-2 text-sm font-medium rounded-md bg-indigo-600 text-white hover:bg-indigo-700"
        >
          + Nouvelle catégorie
        </button>
      </div>

      {deleteError && <p className="text-sm text-rose-600 dark:text-rose-400">{deleteError}</p>}

      <div className="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
        {loading ? (
          <p className="p-5 text-slate-500 dark:text-slate-400 text-sm">Chargement...</p>
        ) : error ? (
          <p className="p-5 text-rose-600 dark:text-rose-400 text-sm">{error}</p>
        ) : categories.length === 0 ? (
          <p className="p-5 text-slate-500 dark:text-slate-400 text-sm">Aucune catégorie pour le moment.</p>
        ) : (
          <ul className="divide-y divide-slate-100 dark:divide-slate-700">
            {categories.map((c) => (
              <li key={c.id} className="flex items-center justify-between px-5 py-3">
                <div className="flex items-center gap-3">
                  <span
                    className="h-4 w-4 rounded-full border border-black/5"
                    style={{ backgroundColor: c.color }}
                  />
                  <span className="text-sm font-medium text-slate-900 dark:text-slate-100">{c.name}</span>
                  <span className="text-xs text-slate-400 dark:text-slate-500 font-mono">{c.color}</span>
                </div>
                <div>
                  <button
                    onClick={() => openEditModal(c)}
                    className="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium mr-3"
                  >
                    Modifier
                  </button>
                  <button
                    onClick={() => setDeleteTarget(c)}
                    className="text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 text-sm font-medium"
                  >
                    Supprimer
                  </button>
                </div>
              </li>
            ))}
          </ul>
        )}
      </div>

      {showModal && (
        <CategoryModal category={editingCategory} onSave={handleSave} onClose={() => setShowModal(false)} />
      )}

      {deleteTarget && (
        <ConfirmDialog
          title="Supprimer la catégorie"
          message={`Voulez-vous vraiment supprimer "${deleteTarget.name}" ?`}
          onConfirm={handleDelete}
          onCancel={() => setDeleteTarget(null)}
        />
      )}
    </div>
  );
}
