<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190504152056 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE work_members_members (id UUID NOT NULL, group_id UUID NOT NULL, email VARCHAR(255) NOT NULL, status VARCHAR(16) NOT NULL, name_first VARCHAR(255) NOT NULL, name_last VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_30039B6DFE54D947 ON work_members_members (group_id)');
        $this->addSql('COMMENT ON COLUMN work_members_members.id IS \'(DC2Type:work_members_member_id)\'');
        $this->addSql('COMMENT ON COLUMN work_members_members.group_id IS \'(DC2Type:work_members_group_id)\'');
        $this->addSql('COMMENT ON COLUMN work_members_members.email IS \'(DC2Type:work_members_member_email)\'');
        $this->addSql('COMMENT ON COLUMN work_members_members.status IS \'(DC2Type:work_members_member_status)\'');
        $this->addSql('ALTER TABLE work_members_members ADD CONSTRAINT FK_30039B6DFE54D947 FOREIGN KEY (group_id) REFERENCES work_members_groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE work_members_members');
    }
}
