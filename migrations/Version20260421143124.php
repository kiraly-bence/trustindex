<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260421143124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add indexes on review.company_name and review.created_at';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX idx_company_name ON review (company_name)');
        $this->addSql('CREATE INDEX idx_created_at ON review (created_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_company_name ON review');
        $this->addSql('DROP INDEX idx_created_at ON review');
    }
}
