interface SummaryCardProps {
  label: string;
  amount: number;
  tone: 'positive' | 'negative' | 'neutral';
}

const toneClasses: Record<SummaryCardProps['tone'], string> = {
  positive: 'text-emerald-600 dark:text-emerald-400',
  negative: 'text-rose-600 dark:text-rose-400',
  neutral: 'text-slate-900 dark:text-slate-100',
};

const formatter = new Intl.NumberFormat('fr-FR', {
  style: 'currency',
  currency: 'EUR',
});

export default function SummaryCard({ label, amount, tone }: SummaryCardProps) {
  return (
    <div className="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm">
      <p className="text-sm text-slate-500 dark:text-slate-400 font-medium">{label}</p>
      <p className={`mt-2 text-2xl font-semibold ${toneClasses[tone]}`}>
        {formatter.format(amount)}
      </p>
    </div>
  );
}
