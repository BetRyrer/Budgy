<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Création initiale du schéma : tables `category` et `transaction`.
 */
final class Version20260709120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création des tables category et transaction';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE category (
                id SERIAL NOT NULL,
                name VARCHAR(100) NOT NULL,
                color VARCHAR(7) NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE transaction (
                id SERIAL NOT NULL,
                category_id INT NOT NULL,
                label VARCHAR(255) NOT NULL,
                amount NUMERIC(10, 2) NOT NULL,
                type VARCHAR(255) NOT NULL,
                date DATE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql('CREATE INDEX IDX_723705D112469DE2 ON transaction (category_id)');
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction
            ADD CONSTRAINT FK_723705D112469DE2
            FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transaction DROP CONSTRAINT FK_723705D112469DE2');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE category');
    }
}
