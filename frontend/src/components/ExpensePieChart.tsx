import { Pie } from 'react-chartjs-2';
import type { CategoryExpense } from '../types';
import { useTheme } from '../ThemeContext';

interface ExpensePieChartProps {
  data: CategoryExpense[];
}

export default function ExpensePieChart({ data }: ExpensePieChartProps) {
  const { theme } = useTheme();

  if (data.length === 0) {
    return (
      <p className="text-sm text-slate-500 dark:text-slate-400 flex items-center justify-center h-64">
        Aucune dépense à afficher.
      </p>
    );
  }

  const chartData = {
    labels: data.map((c) => c.name),
    datasets: [
      {
        data: data.map((c) => c.total),
        backgroundColor: data.map((c) => c.color),
        borderWidth: 1,
        borderColor: theme === 'dark' ? '#1e293b' : '#ffffff',
      },
    ],
  };

  const legendColor = theme === 'dark' ? '#cbd5e1' : '#475569';

  return (
    <div className="h-64">
      <Pie
        data={chartData}
        options={{
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'right', labels: { boxWidth: 12, color: legendColor } },
          },
        }}
      />
    </div>
  );
}
