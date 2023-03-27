<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230327101456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE requirement DROP FOREIGN KEY FK_DB3F555049E57DC5');
        $this->addSql('DROP INDEX IDX_DB3F555049E57DC5 ON requirement');
        $this->addSql('ALTER TABLE requirement DROP requirement_item_id');
        $this->addSql('ALTER TABLE requirement_item ADD requirement_id INT NOT NULL');
        $this->addSql('ALTER TABLE requirement_item ADD CONSTRAINT FK_CB05DB277B576F77 FOREIGN KEY (requirement_id) REFERENCES requirement (id)');
        $this->addSql('CREATE INDEX IDX_CB05DB277B576F77 ON requirement_item (requirement_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE requirement ADD requirement_item_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE requirement ADD CONSTRAINT FK_DB3F555049E57DC5 FOREIGN KEY (requirement_item_id) REFERENCES requirement_item (id)');
        $this->addSql('CREATE INDEX IDX_DB3F555049E57DC5 ON requirement (requirement_item_id)');
        $this->addSql('ALTER TABLE requirement_item DROP FOREIGN KEY FK_CB05DB277B576F77');
        $this->addSql('DROP INDEX IDX_CB05DB277B576F77 ON requirement_item');
        $this->addSql('ALTER TABLE requirement_item DROP requirement_id');
    }
}
