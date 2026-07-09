import type { ReactNode } from 'react';
import { NavLink } from './NavLink';
import { useTheme } from '../ThemeContext';

interface LayoutProps {
  page: 'dashboard' | 'transactions' | 'categories';
  onNavigate: (page: 'dashboard' | 'transactions' | 'categories') => void;
  children: ReactNode;
}

export default function Layout({ page, onNavigate, children }: LayoutProps) {
  const { theme, toggleTheme } = useTheme();

  return (
    <div className="min-h-screen flex flex-col">
      <header className="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 sticky top-0 z-10">
        <div className="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <div className="h-8 w-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold">
              B
            </div>
            <span className="text-lg font-semibold text-slate-900 dark:text-slate-100">Budgy</span>
          </div>
          <div className="flex items-center gap-2">
            <nav className="flex gap-1">
              <NavLink active={page === 'dashboard'} onClick={() => onNavigate('dashboard')}>
                Dashboard
              </NavLink>
              <NavLink active={page === 'transactions'} onClick={() => onNavigate('transactions')}>
                Transactions
              </NavLink>
              <NavLink active={page === 'categories'} onClick={() => onNavigate('categories')}>
                Catégories
              </NavLink>
            </nav>
            <button
              onClick={toggleTheme}
              aria-label="Changer de thème"
              title={theme === 'dark' ? 'Passer en mode clair' : 'Passer en mode sombre'}
              className="ml-2 h-9 w-9 flex items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-100"
            >
              {theme === 'dark' ? (
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="h-5 w-5">
                  <circle cx="12" cy="12" r="4" />
                  <path
                    strokeLinecap="round"
                    d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"
                  />
                </svg>
              ) : (
                <svg viewBox="0 0 24 24" fill="currentColor" className="h-5 w-5">
                  <path d="M20.742 13.045A8.088 8.088 0 0 1 18.5 13.5c-4.418 0-8-3.582-8-8 0-.79.114-1.553.327-2.273a.75.75 0 0 0-.977-.926A10.5 10.5 0 1 0 21.75 14a.75.75 0 0 0-1.008-.955Z" />
                </svg>
              )}
            </button>
          </div>
        </div>
      </header>
      <main className="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 py-6">{children}</main>
    </div>
  );
}
