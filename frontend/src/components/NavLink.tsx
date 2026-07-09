import type { ReactNode } from 'react';

interface NavLinkProps {
  active: boolean;
  onClick: () => void;
  children: ReactNode;
}

export function NavLink({ active, onClick, children }: NavLinkProps) {
  return (
    <button
      onClick={onClick}
      className={`px-3 py-2 rounded-md text-sm font-medium transition-colors ${
        active
          ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400'
          : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-100'
      }`}
    >
      {children}
    </button>
  );
}
