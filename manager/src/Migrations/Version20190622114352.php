<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190622114352 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE work_projects_task_changes (id INT NOT NULL, task_id INT NOT NULL, actor_id UUID NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, set_project_id UUID DEFAULT NULL, set_name VARCHAR(255) DEFAULT NULL, set_content TEXT DEFAULT NULL, set_file_id UUID DEFAULT NULL, set_removed_file_id UUID DEFAULT NULL, set_type VARCHAR(16) DEFAULT NULL, set_status VARCHAR(255) DEFAULT NULL, set_progress SMALLINT DEFAULT NULL, set_priority SMALLINT DEFAULT NULL, set_parent_id INT DEFAULT NULL, set_removed_parent BOOLEAN DEFAULT NULL, set_plan DATE DEFAULT NULL, set_removed_plan BOOLEAN DEFAULT NULL, set_executor_id UUID DEFAULT NULL, set_revoked_executor_id UUID DEFAULT NULL, PRIMARY KEY(task_id, id))');
        $this->addSql('CREATE INDEX IDX_D8BE114A8DB60186 ON work_projects_task_changes (task_id)');
        $this->addSql('CREATE INDEX IDX_D8BE114A10DAF24A ON work_projects_task_changes (actor_id)');
        $this->addSql('COMMENT ON COLUMN work_projects_task_changes.id IS \'(DC2Type:work_projects_task_change_id)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_task_changes.task_id IS \'(DC2Type:work_projects_task_id)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_task_changes.actor_id IS \'(DC2Type:work_members_member_id)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_task_changes.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_task_changes.set_project_id IS \'(DC2Type:work_projects_project_id)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_task_changes.set_file_id IS \'(DC2Type:work_projects_task_file_id)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_task_changes.set_removed_file_id IS \'(DC2Type:work_projects_task_file_id)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_task_changes.set_type IS \'(DC2Type:work_projects_task_type)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_task_changes.set_status IS \'(DC2Type:work_projects_task_status)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_task_changes.set_parent_id IS \'(DC2Type:work_projects_task_id)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_task_changes.set_plan IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_task_changes.set_executor_id IS \'(DC2Type:work_members_member_id)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_task_changes.set_revoked_executor_id IS \'(DC2Type:work_members_member_id)\'');
        $this->addSql('ALTER TABLE work_projects_task_changes ADD CONSTRAINT FK_D8BE114A8DB60186 FOREIGN KEY (task_id) REFERENCES work_projects_tasks (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE work_projects_task_changes ADD CONSTRAINT FK_D8BE114A10DAF24A FOREIGN KEY (actor_id) REFERENCES work_members_members (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE work_projects_task_changes');
    }
}
