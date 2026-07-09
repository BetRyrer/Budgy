<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Repository\CategoryRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/transactions')]
class TransactionController extends AbstractApiController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TransactionRepository $transactions,
        private readonly CategoryRepository $categories,
        private readonly ValidatorInterface $validator,
    ) {
    }

    /** Champs autorisés pour le tri, mappés vers l'alias DQL correspondant. */
    private const SORTABLE_FIELDS = [
        'date' => 't.date',
        'label' => 't.label',
        'amount' => 't.amount',
    ];

    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $qb = $this->transactions->createQueryBuilder('t');

        // Filtre par mois, ex: ?month=2026-07
        $month = $request->query->get('month');
        if ($month !== null) {
            $start = \DateTimeImmutable::createFromFormat('Y-m-d', $month.'-01');
            if ($start === false) {
                return $this->badRequest('Le paramètre "month" doit être au format AAAA-MM.');
            }
            $end = $start->modify('last day of this month');
            $qb->andWhere('t.date >= :start AND t.date <= :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }

        // Filtre par catégorie, ex: ?category=3
        $categoryId = $request->query->get('category');
        if ($categoryId !== null) {
            $qb->andWhere('t.category = :categoryId')->setParameter('categoryId', $categoryId);
        }

        // Recherche texte sur le libellé, ex: ?search=courses
        $search = $request->query->get('search');
        if ($search !== null && $search !== '') {
            $qb->andWhere('LOWER(t.label) LIKE LOWER(:search)')
                ->setParameter('search', '%'.$search.'%');
        }

        // Tri, ex: ?sort=amount&order=asc (par défaut : date décroissante)
        $sort = $request->query->get('sort', 'date');
        $order = strtolower((string) $request->query->get('order', 'desc')) === 'asc' ? 'ASC' : 'DESC';
        if (!isset(self::SORTABLE_FIELDS[$sort])) {
            return $this->badRequest('Le paramètre "sort" doit valoir "date", "label" ou "amount".');
        }
        $qb->addOrderBy(self::SORTABLE_FIELDS[$sort], $order)
            ->addOrderBy('t.id', $order);

        $results = $qb->getQuery()->getResult();

        return $this->json(array_map(fn (Transaction $t) => $t->toArray(), $results));
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $transaction = $this->transactions->find($id);
        if (!$transaction) {
            return $this->notFound('Transaction introuvable.');
        }

        return $this->json($transaction->toArray());
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = $this->decode($request);
        if ($data === null) {
            return $this->badRequest('Corps de requête JSON invalide.');
        }

        $transaction = new Transaction();
        $errorResponse = $this->hydrate($transaction, $data);
        if ($errorResponse) {
            return $errorResponse;
        }

        if ($errorResponse = $this->validate($transaction, $this->validator)) {
            return $errorResponse;
        }

        $this->em->persist($transaction);
        $this->em->flush();

        return $this->json($transaction->toArray(), 201);
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $transaction = $this->transactions->find($id);
        if (!$transaction) {
            return $this->notFound('Transaction introuvable.');
        }

        $data = $this->decode($request);
        if ($data === null) {
            return $this->badRequest('Corps de requête JSON invalide.');
        }

        $errorResponse = $this->hydrate($transaction, $data);
        if ($errorResponse) {
            return $errorResponse;
        }

        if ($errorResponse = $this->validate($transaction, $this->validator)) {
            return $errorResponse;
        }

        $this->em->flush();

        return $this->json($transaction->toArray());
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $transaction = $this->transactions->find($id);
        if (!$transaction) {
            return $this->notFound('Transaction introuvable.');
        }

        $this->em->remove($transaction);
        $this->em->flush();

        return $this->json(null, 204);
    }

    /**
     * Applique les champs présents dans $data à la transaction. Renvoie une JsonResponse
     * d'erreur si un champ est malformé (type, date, catégorie inexistante), sinon null.
     */
    private function hydrate(Transaction $transaction, array $data): ?JsonResponse
    {
        if (array_key_exists('label', $data)) {
            $transaction->setLabel((string) $data['label']);
        }

        if (array_key_exists('amount', $data)) {
            if (!is_numeric($data['amount'])) {
                return $this->badRequest('Le champ "amount" doit être numérique.');
            }
            $transaction->setAmount(number_format((float) $data['amount'], 2, '.', ''));
        }

        if (array_key_exists('type', $data)) {
            $type = TransactionType::tryFrom((string) $data['type']);
            if ($type === null) {
                return $this->badRequest('Le champ "type" doit valoir "income" ou "expense".');
            }
            $transaction->setType($type);
        }

        if (array_key_exists('date', $data)) {
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $data['date']);
            if ($date === false) {
                return $this->badRequest('Le champ "date" doit être au format AAAA-MM-JJ.');
            }
            $transaction->setDate($date);
        }

        if (array_key_exists('categoryId', $data)) {
            $category = $this->categories->find($data['categoryId']);
            if ($category === null) {
                return $this->badRequest('Catégorie introuvable pour "categoryId".');
            }
            $transaction->setCategory($category);
        }

        return null;
    }
}
