<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/stats')]
class StatsController extends AbstractApiController
{
    public function __construct(private readonly TransactionRepository $transactions)
    {
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $totalIncome = $this->transactions->sumByType(TransactionType::Income);
        $totalExpense = $this->transactions->sumByType(TransactionType::Expense);

        $monthStart = new \DateTimeImmutable('first day of this month');
        $monthEnd = new \DateTimeImmutable('last day of this month');

        $monthIncome = $this->transactions->sumByType(TransactionType::Income, $monthStart, $monthEnd);
        $monthExpense = $this->transactions->sumByType(TransactionType::Expense, $monthStart, $monthEnd);

        return $this->json([
            // Totaux sur l'ensemble des transactions
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $totalIncome - $totalExpense,

            // Totaux du mois en cours uniquement
            'currentMonth' => [
                'month' => $monthStart->format('Y-m'),
                'income' => $monthIncome,
                'expense' => $monthExpense,
                'balance' => $monthIncome - $monthExpense,
            ],

            // Répartition des dépenses par catégorie (toutes périodes confondues)
            'expensesByCategory' => $this->transactions->expensesByCategory(),

            // Évolution des 6 derniers mois, pour le graphique d'évolution
            'monthlyEvolution' => $this->transactions->monthlyEvolution(6),

            // Dépense la plus récente
            'lastExpense' => $this->transactions->lastExpense()?->toArray(),

            // Les 5 dernières transactions (revenus et dépenses confondus)
            'recentTransactions' => array_map(
                fn (Transaction $t) => $t->toArray(),
                $this->transactions->recent(5),
            ),
        ]);
    }
}
