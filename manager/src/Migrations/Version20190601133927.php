<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190601133927 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE work_projects_tasks (id INT NOT NULL, project_id UUID NOT NULL, author_id UUID NOT NULL, parent_id INT DEFAULT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, plan_date DATE DEFAULT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, name VARCHAR(255) NOT NULL, content TEXT DEFAULT NULL, type VARCHAR(16) NOT NULL, progress SMALLINT NOT NULL, priority SMALLINT NOT NULL, status VARCHAR(16) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E42D1865166D1F9C ON work_projects_tasks (project_id)');
        $this->addSql('CREATE INDEX IDX_E42D1865F675F31B ON work_projects_tasks (author_id)');
        $this->addSql('CREATE INDEX IDX_E42D1865727ACA70 ON work_projects_tasks (parent_id)');
        $this->addSql('CREATE INDEX IDX_E42D1865AA9E377A ON work_projects_tasks (date)');
        $this->addSql('COMMENT ON COLUMN work_projects_tasks.id IS \'(DC2Type:work_projects_task_id)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_tasks.project_id IS \'(DC2Type:work_projects_project_id)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_tasks.author_id IS \'(DC2Type:work_members_member_id)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_tasks.parent_id IS \'(DC2Type:work_projects_task_id)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_tasks.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_tasks.plan_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_tasks.start_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_tasks.end_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_tasks.type IS \'(DC2Type:work_projects_task_type)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_tasks.status IS \'(DC2Type:work_projects_task_status)\'');
        $this->addSql('CREATE TABLE work_projects_tasks_executors (task_id INT NOT NULL, member_id UUID NOT NULL, PRIMARY KEY(task_id, member_id))');
        $this->addSql('CREATE INDEX IDX_6291D08E8DB60186 ON work_projects_tasks_executors (task_id)');
        $this->addSql('CREATE INDEX IDX_6291D08E7597D3FE ON work_projects_tasks_executors (member_id)');
        $this->addSql('COMMENT ON COLUMN work_projects_tasks_executors.task_id IS \'(DC2Type:work_projects_task_id)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_tasks_executors.member_id IS \'(DC2Type:work_members_member_id)\'');
        $this->addSql('ALTER TABLE work_projects_tasks ADD CONSTRAINT FK_E42D1865166D1F9C FOREIGN KEY (project_id) REFERENCES work_projects_projects (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE work_projects_tasks ADD CONSTRAINT FK_E42D1865F675F31B FOREIGN KEY (author_id) REFERENCES work_members_members (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE work_projects_tasks ADD CONSTRAINT FK_E42D1865727ACA70 FOREIGN KEY (parent_id) REFERENCES work_projects_tasks (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE work_projects_tasks_executors ADD CONSTRAINT FK_6291D08E8DB60186 FOREIGN KEY (task_id) REFERENCES work_projects_tasks (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE work_projects_tasks_executors ADD CONSTRAINT FK_6291D08E7597D3FE FOREIGN KEY (member_id) REFERENCES work_members_members (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE work_projects_tasks DROP CONSTRAINT FK_E42D1865727ACA70');
        $this->addSql('ALTER TABLE work_projects_tasks_executors DROP CONSTRAINT FK_6291D08E8DB60186');
        $this->addSql('DROP TABLE work_projects_tasks');
        $this->addSql('DROP TABLE work_projects_tasks_executors');
    }
}
