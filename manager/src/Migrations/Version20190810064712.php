<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190810064712 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE user_users ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE comment_comments ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE work_projects_projects ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE work_members_members ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE work_members_groups ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE work_projects_roles ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE work_projects_tasks ADD version INT DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE user_users DROP version');
        $this->addSql('ALTER TABLE work_members_groups DROP version');
        $this->addSql('ALTER TABLE work_members_members DROP version');
        $this->addSql('ALTER TABLE work_projects_projects DROP version');
        $this->addSql('ALTER TABLE work_projects_roles DROP version');
        $this->addSql('ALTER TABLE work_projects_tasks DROP version');
        $this->addSql('ALTER TABLE comment_comments DROP version');
    }
}
