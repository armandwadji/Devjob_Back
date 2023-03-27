<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230327080811 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE requirement (id INT AUTO_INCREMENT NOT NULL, offer_id INT NOT NULL, requirement_item_id INT DEFAULT NULL, content LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_DB3F555053C674EE (offer_id), INDEX IDX_DB3F555049E57DC5 (requirement_item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE requirement_item (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE requirement ADD CONSTRAINT FK_DB3F555053C674EE FOREIGN KEY (offer_id) REFERENCES offer (id)');
        $this->addSql('ALTER TABLE requirement ADD CONSTRAINT FK_DB3F555049E57DC5 FOREIGN KEY (requirement_item_id) REFERENCES requirement_item (id)');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873E2576E0FD FOREIGN KEY (contract_id) REFERENCES contract (id)');
        $this->addSql('CREATE INDEX IDX_29D6873E2576E0FD ON offer (contract_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE requirement DROP FOREIGN KEY FK_DB3F555053C674EE');
        $this->addSql('ALTER TABLE requirement DROP FOREIGN KEY FK_DB3F555049E57DC5');
        $this->addSql('DROP TABLE requirement');
        $this->addSql('DROP TABLE requirement_item');
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873E2576E0FD');
        $this->addSql('DROP INDEX IDX_29D6873E2576E0FD ON offer');
    }
}
