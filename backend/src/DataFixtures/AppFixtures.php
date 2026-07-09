<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    /** @var array<string, Category> */
    private array $categories = [];

    public function load(ObjectManager $manager): void
    {
        $this->loadCategories($manager);
        $this->loadTransactions($manager);

        $manager->flush();
    }

    private function loadCategories(ObjectManager $manager): void
    {
        $definitions = [
            'Salaire' => '#22c55e',
            'Freelance' => '#84cc16',
            'Alimentation' => '#f97316',
            'Logement' => '#0ea5e9',
            'Transport' => '#6366f1',
            'Loisirs' => '#ec4899',
            'Santé' => '#14b8a6',
            'Abonnements' => '#a855f7',
        ];

        foreach ($definitions as $name => $color) {
            $category = new Category();
            $category->setName($name);
            $category->setColor($color);
            $manager->persist($category);
            $this->categories[$name] = $category;
        }
    }

    private function loadTransactions(ObjectManager $manager): void
    {
        $today = new \DateTimeImmutable('today');

        // (label, montant, type, catégorie, nombre de mois en arrière, jour du mois)
        $definitions = [
            ['Salaire mensuel', '2800.00', TransactionType::Income, 'Salaire', 0, 1],
            ['Salaire mensuel', '2800.00', TransactionType::Income, 'Salaire', 1, 1],
            ['Salaire mensuel', '2800.00', TransactionType::Income, 'Salaire', 2, 1],
            ['Mission freelance - site vitrine', '650.00', TransactionType::Income, 'Freelance', 0, 12],
            ['Mission freelance - refonte logo', '400.00', TransactionType::Income, 'Freelance', 1, 18],
            ['Remboursement mutuelle', '80.00', TransactionType::Income, 'Santé', 1, 20],

            ['Loyer', '950.00', TransactionType::Expense, 'Logement', 0, 3],
            ['Loyer', '950.00', TransactionType::Expense, 'Logement', 1, 3],
            ['Loyer', '950.00', TransactionType::Expense, 'Logement', 2, 3],
            ['Électricité', '75.30', TransactionType::Expense, 'Logement', 0, 5],
            ['Électricité', '68.90', TransactionType::Expense, 'Logement', 1, 5],
            ['Internet', '35.00', TransactionType::Expense, 'Abonnements', 0, 7],
            ['Internet', '35.00', TransactionType::Expense, 'Abonnements', 1, 7],
            ['Netflix', '15.99', TransactionType::Expense, 'Abonnements', 0, 10],
            ['Spotify', '9.99', TransactionType::Expense, 'Abonnements', 0, 10],
            ['Courses Carrefour', '87.45', TransactionType::Expense, 'Alimentation', 0, 2],
            ['Courses Carrefour', '92.10', TransactionType::Expense, 'Alimentation', 0, 9],
            ['Courses Carrefour', '78.60', TransactionType::Expense, 'Alimentation', 0, 16],
            ['Boulangerie', '12.40', TransactionType::Expense, 'Alimentation', 0, 4],
            ['Restaurant italien', '54.00', TransactionType::Expense, 'Alimentation', 1, 14],
            ['Courses Carrefour', '81.20', TransactionType::Expense, 'Alimentation', 1, 8],
            ['Essence', '65.00', TransactionType::Expense, 'Transport', 0, 6],
            ['Essence', '62.50', TransactionType::Expense, 'Transport', 1, 6],
            ['Pass Navigo', '75.20', TransactionType::Expense, 'Transport', 0, 1],
            ['Pass Navigo', '75.20', TransactionType::Expense, 'Transport', 1, 1],
            ['Réparation vélo', '40.00', TransactionType::Expense, 'Transport', 2, 22],
            ['Cinéma', '13.50', TransactionType::Expense, 'Loisirs', 0, 15],
            ['Concert', '45.00', TransactionType::Expense, 'Loisirs', 1, 25],
            ['Livre', '18.90', TransactionType::Expense, 'Loisirs', 0, 19],
            ['Salle de sport', '29.99', TransactionType::Expense, 'Loisirs', 0, 3],
            ['Salle de sport', '29.99', TransactionType::Expense, 'Loisirs', 1, 3],
            ['Pharmacie', '22.30', TransactionType::Expense, 'Santé', 0, 11],
            ['Consultation médecin', '25.00', TransactionType::Expense, 'Santé', 2, 17],
        ];

        foreach ($definitions as [$label, $amount, $type, $categoryName, $monthsAgo, $day]) {
            $date = $today->modify(sprintf('-%d months', $monthsAgo))->modify('first day of this month')->modify(sprintf('+%d days', $day - 1));

            $transaction = new Transaction();
            $transaction->setLabel($label);
            $transaction->setAmount($amount);
            $transaction->setType($type);
            $transaction->setDate($date);
            $transaction->setCategory($this->categories[$categoryName]);

            $manager->persist($transaction);
        }
    }
}
