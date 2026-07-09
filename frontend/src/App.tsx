import { useState } from 'react';
import Layout from './components/Layout';
import Dashboard from './pages/Dashboard';
import Transactions from './pages/Transactions';
import Categories from './pages/Categories';

type Page = 'dashboard' | 'transactions' | 'categories';

export default function App() {
  const [page, setPage] = useState<Page>('dashboard');

  return (
    <Layout page={page} onNavigate={setPage}>
      {page === 'dashboard' && <Dashboard />}
      {page === 'transactions' && <Transactions />}
      {page === 'categories' && <Categories />}
    </Layout>
  );
}
