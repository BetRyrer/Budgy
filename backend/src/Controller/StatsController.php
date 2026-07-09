<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Entity\User;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/stats')]
class StatsController extends AbstractApiController
{
    public function __construct(private readonly TransactionRepository $transactions)
    {
    }

    #[Route('', methods: ['GET'])]
    public function index(#[CurrentUser] User $user): JsonResponse
    {
        $totalIncome = $this->transactions->sumByType($user, TransactionType::Income);
        $totalExpense = $this->transactions->sumByType($user, TransactionType::Expense);

        $monthStart = new \DateTimeImmutable('first day of this month');
        $monthEnd = new \DateTimeImmutable('last day of this month');

        $monthIncome = $this->transactions->sumByType($user, TransactionType::Income, $monthStart, $monthEnd);
        $monthExpense = $this->transactions->sumByType($user, TransactionType::Expense, $monthStart, $monthEnd);

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
            'expensesByCategory' => $this->transactions->expensesByCategory($user),

            // Évolution des 6 derniers mois, pour le graphique d'évolution
            'monthlyEvolution' => $this->transactions->monthlyEvolution($user, 6),

            // Dépense la plus récente
            'lastExpense' => $this->transactions->lastExpense($user)?->toArray(),

            // Les 5 dernières transactions (revenus et dépenses confondus)
            'recentTransactions' => array_map(
                fn (Transaction $t) => $t->toArray(),
                $this->transactions->recent($user, 5),
            ),
        ]);
    }
}
