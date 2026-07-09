<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @return array<int, Category>
     */
    public function findAllForOwner(User $owner): array
    {
        return $this->findBy(['owner' => $owner], ['name' => 'ASC']);
    }

    public function findOneForOwner(int $id, User $owner): ?Category
    {
        return $this->findOneBy(['id' => $id, 'owner' => $owner]);
    }
}
