<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Controller\Core\Migrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191108195910 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {

        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->isTransactional();

        $this->connection->executeQuery('CREATE TABLE IF NOT EXISTS my_schedule_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, icon VARCHAR(255) NOT NULL, deleted TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->connection->executeQuery('CREATE TABLE IF NOT EXISTS my_schedule (id INT AUTO_INCREMENT NOT NULL, schedule_type_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, date DATE DEFAULT NULL, is_date_based TINYINT(1) NOT NULL, information VARCHAR(255) DEFAULT NULL, deleted TINYINT(1) NOT NULL, INDEX IDX_686A84B44826A022 (schedule_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfConstraintDoesNotExist(
            Migrations::CONSTRAINT_TYPE_FOREIGN_KEY,
            'FK_686A84B44826A022',
            'ALTER TABLE my_schedule ADD CONSTRAINT FK_686A84B44826A022 FOREIGN KEY (schedule_type_id) REFERENCES my_schedule_type (id)'
        ));

        // Transferring my_car into schedules
        $this->connection->executeQuery("
            INSERT INTO `my_schedule_type` (`id`, `name`, `icon`, `deleted`) 
                VALUES (1, 'car', 'fas fa-car', 0)"
        );

        $this->connection->executeQuery(Migrations::buildSqlExecutedIfColumnExist('schedule_type_id', 'my_schedule', '
            INSERT INTO my_schedule
            (
                SELECT
                    NULL            AS id,
                    1               AS schedule_type_id,
                    name            AS name,
                    date            AS date,
                        CASE
                            WHEN date IS NULL THEN 0
                            ELSE 1
                        END         AS is_date_based,
                    information     AS information,
                    deleted         AS deleted
                
                FROM my_car
            )
        '));

        $this->connection->executeQuery("DROP TABLE IF EXISTS my_car");
        $this->connection->executeQuery("DROP TABLE IF EXISTS my_car_schedules_types");

        $this->addSql(Migrations::getSuccessInformationSql());
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->connection->executeQuery('ALTER TABLE my_schedule DROP FOREIGN KEY FK_686A84B44826A022');
        $this->connection->executeQuery('DROP TABLE my_schedule_type');
        $this->connection->executeQuery('DROP TABLE my_schedule');
    }
}
