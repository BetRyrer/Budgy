<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260709222141 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la table user et rattache category/transaction à leur propriétaire';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_email ON "user" (email)');
        $this->addSql('ALTER TABLE category ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE category ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C17E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_64C19C17E3C61F9 ON category (owner_id)');
        $this->addSql('ALTER TABLE transaction ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE transaction ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE transaction ALTER date TYPE DATE');
        $this->addSql('COMMENT ON COLUMN transaction.date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D17E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_723705D17E3C61F9 ON transaction (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE category DROP CONSTRAINT FK_64C19C17E3C61F9');
        $this->addSql('ALTER TABLE transaction DROP CONSTRAINT FK_723705D17E3C61F9');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP INDEX IDX_64C19C17E3C61F9');
        $this->addSql('ALTER TABLE category DROP owner_id');
        $this->addSql('CREATE SEQUENCE category_id_seq');
        $this->addSql('SELECT setval(\'category_id_seq\', (SELECT MAX(id) FROM category))');
        $this->addSql('ALTER TABLE category ALTER id SET DEFAULT nextval(\'category_id_seq\')');
        $this->addSql('DROP INDEX IDX_723705D17E3C61F9');
        $this->addSql('ALTER TABLE transaction DROP owner_id');
        $this->addSql('CREATE SEQUENCE transaction_id_seq');
        $this->addSql('SELECT setval(\'transaction_id_seq\', (SELECT MAX(id) FROM transaction))');
        $this->addSql('ALTER TABLE transaction ALTER id SET DEFAULT nextval(\'transaction_id_seq\')');
        $this->addSql('ALTER TABLE transaction ALTER date TYPE DATE');
        $this->addSql('COMMENT ON COLUMN transaction.date IS NULL');
    }
}
