<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230330194920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE candidate_offer (candidate_id INT NOT NULL, offer_id INT NOT NULL, INDEX IDX_11366CC291BD8781 (candidate_id), INDEX IDX_11366CC253C674EE (offer_id), PRIMARY KEY(candidate_id, offer_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE candidate_offer ADD CONSTRAINT FK_11366CC291BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE candidate_offer ADD CONSTRAINT FK_11366CC253C674EE FOREIGN KEY (offer_id) REFERENCES offer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE candidate DROP FOREIGN KEY FK_C8B28E4453C674EE');
        $this->addSql('DROP INDEX IDX_C8B28E4453C674EE ON candidate');
        $this->addSql('ALTER TABLE candidate DROP offer_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate_offer DROP FOREIGN KEY FK_11366CC291BD8781');
        $this->addSql('ALTER TABLE candidate_offer DROP FOREIGN KEY FK_11366CC253C674EE');
        $this->addSql('DROP TABLE candidate_offer');
        $this->addSql('ALTER TABLE candidate ADD offer_id INT NOT NULL');
        $this->addSql('ALTER TABLE candidate ADD CONSTRAINT FK_C8B28E4453C674EE FOREIGN KEY (offer_id) REFERENCES offer (id)');
        $this->addSql('CREATE INDEX IDX_C8B28E4453C674EE ON candidate (offer_id)');
    }
}
