import { Line } from 'react-chartjs-2';
import type { MonthlyEvolutionEntry } from '../types';
import { useTheme } from '../ThemeContext';

interface MonthlyChartProps {
  data: MonthlyEvolutionEntry[];
}

function formatMonth(month: string): string {
  const [year, m] = month.split('-');
  const date = new Date(Number(year), Number(m) - 1, 1);
  return date.toLocaleDateString('fr-FR', { month: 'short', year: '2-digit' });
}

export default function MonthlyChart({ data }: MonthlyChartProps) {
  const { theme } = useTheme();
  const tickColor = theme === 'dark' ? '#cbd5e1' : '#475569';
  const gridColor = theme === 'dark' ? 'rgba(148, 163, 184, 0.15)' : 'rgba(100, 116, 139, 0.1)';

  const chartData = {
    labels: data.map((d) => formatMonth(d.month)),
    datasets: [
      {
        label: 'Revenus',
        data: data.map((d) => d.income),
        borderColor: '#22c55e',
        backgroundColor: '#22c55e',
        tension: 0.3,
      },
      {
        label: 'Dépenses',
        data: data.map((d) => d.expense),
        borderColor: '#f43f5e',
        backgroundColor: '#f43f5e',
        tension: 0.3,
      },
    ],
  };

  return (
    <div className="h-64">
      <Line
        data={chartData}
        options={{
          maintainAspectRatio: false,
          plugins: { legend: { position: 'bottom', labels: { color: tickColor } } },
          scales: {
            x: { ticks: { color: tickColor }, grid: { color: gridColor } },
            y: { beginAtZero: true, ticks: { color: tickColor }, grid: { color: gridColor } },
          },
        }}
      />
    </div>
  );
}
