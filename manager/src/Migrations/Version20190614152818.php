<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190614152818 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE work_projects_task_files (id UUID NOT NULL, task_id INT NOT NULL, member_id UUID NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, info_path VARCHAR(255) NOT NULL, info_name VARCHAR(255) NOT NULL, info_size INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B8A3E1028DB60186 ON work_projects_task_files (task_id)');
        $this->addSql('CREATE INDEX IDX_B8A3E1027597D3FE ON work_projects_task_files (member_id)');
        $this->addSql('CREATE INDEX IDX_B8A3E102AA9E377A ON work_projects_task_files (date)');
        $this->addSql('COMMENT ON COLUMN work_projects_task_files.id IS \'(DC2Type:work_projects_task_file_id)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_task_files.task_id IS \'(DC2Type:work_projects_task_id)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_task_files.member_id IS \'(DC2Type:work_members_member_id)\'');
        $this->addSql('COMMENT ON COLUMN work_projects_task_files.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE work_projects_task_files ADD CONSTRAINT FK_B8A3E1028DB60186 FOREIGN KEY (task_id) REFERENCES work_projects_tasks (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE work_projects_task_files ADD CONSTRAINT FK_B8A3E1027597D3FE FOREIGN KEY (member_id) REFERENCES work_members_members (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE work_projects_task_files');
    }
}
