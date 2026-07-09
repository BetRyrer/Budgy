<?php

namespace App\Repository;

use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * Somme des montants pour un type donné, optionnellement bornée par des dates (incluses).
     */
    public function sumByType(User $owner, TransactionType $type, ?\DateTimeImmutable $from = null, ?\DateTimeImmutable $to = null): float
    {
        $qb = $this->createQueryBuilder('t')
            ->select('COALESCE(SUM(t.amount), 0) as total')
            ->andWhere('t.owner = :owner')
            ->andWhere('t.type = :type')
            ->setParameter('owner', $owner)
            ->setParameter('type', $type->value);

        if ($from !== null) {
            $qb->andWhere('t.date >= :from')->setParameter('from', $from);
        }
        if ($to !== null) {
            $qb->andWhere('t.date <= :to')->setParameter('to', $to);
        }

        return (float) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Répartition des dépenses par catégorie (nom, couleur, total) pour une période optionnelle.
     *
     * @return array<int, array{id: int, name: string, color: string, total: float}>
     */
    public function expensesByCategory(User $owner, ?\DateTimeImmutable $from = null, ?\DateTimeImmutable $to = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->select('c.id as id, c.name as name, c.color as color, COALESCE(SUM(t.amount), 0) as total')
            ->join('t.category', 'c')
            ->andWhere('t.owner = :owner')
            ->andWhere('t.type = :type')
            ->setParameter('owner', $owner)
            ->setParameter('type', TransactionType::Expense->value)
            ->groupBy('c.id, c.name, c.color')
            ->orderBy('total', 'DESC');

        if ($from !== null) {
            $qb->andWhere('t.date >= :from')->setParameter('from', $from);
        }
        if ($to !== null) {
            $qb->andWhere('t.date <= :to')->setParameter('to', $to);
        }

        $rows = $qb->getQuery()->getArrayResult();

        return array_map(static fn (array $row) => [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'color' => $row['color'],
            'total' => (float) $row['total'],
        ], $rows);
    }

    /**
     * Évolution mensuelle (revenus / dépenses) sur les N derniers mois, mois courant inclus.
     *
     * @return array<int, array{month: string, income: float, expense: float}>
     */
    public function monthlyEvolution(User $owner, int $months = 6): array
    {
        $now = new \DateTimeImmutable('first day of this month');
        $start = $now->modify(sprintf('-%d months', $months - 1));

        // Le regroupement par mois se fait en PHP plutôt qu'en DQL : les fonctions de
        // formatage de date (CAST/SUBSTRING) ne sont pas portables/supportées nativement
        // par le parseur DQL, alors qu'agréger ~quelques centaines de lignes en PHP est trivial.
        $rows = $this->createQueryBuilder('t')
            ->select('t.date as date, t.type as type, t.amount as amount')
            ->andWhere('t.owner = :owner')
            ->andWhere('t.date >= :start')
            ->setParameter('owner', $owner)
            ->setParameter('start', $start)
            ->getQuery()
            ->getArrayResult();

        // Initialise tous les mois de la période à zéro pour ne pas avoir de trous dans le graphique
        $result = [];
        for ($i = 0; $i < $months; ++$i) {
            $key = $start->modify(sprintf('+%d months', $i))->format('Y-m');
            $result[$key] = ['month' => $key, 'income' => 0.0, 'expense' => 0.0];
        }

        foreach ($rows as $row) {
            /** @var \DateTimeImmutable $date */
            $date = $row['date'];
            $key = $date->format('Y-m');
            if (!isset($result[$key])) {
                continue;
            }
            $type = $row['type'] instanceof TransactionType ? $row['type']->value : $row['type'];
            if ($type === TransactionType::Income->value) {
                $result[$key]['income'] += (float) $row['amount'];
            } else {
                $result[$key]['expense'] += (float) $row['amount'];
            }
        }

        return array_values($result);
    }

    /**
     * Dépense la plus récente (par date, puis par id en cas d'égalité), ou null s'il n'y en a aucune.
     */
    public function lastExpense(User $owner): ?Transaction
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.owner = :owner')
            ->andWhere('t.type = :type')
            ->setParameter('owner', $owner)
            ->setParameter('type', TransactionType::Expense->value)
            ->orderBy('t.date', 'DESC')
            ->addOrderBy('t.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Les N transactions les plus récentes, tous types confondus.
     *
     * @return array<int, Transaction>
     */
    public function recent(User $owner, int $limit = 5): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('t.date', 'DESC')
            ->addOrderBy('t.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findOneForOwner(int $id, User $owner): ?Transaction
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.id = :id')
            ->andWhere('t.owner = :owner')
            ->setParameter('id', $id)
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
