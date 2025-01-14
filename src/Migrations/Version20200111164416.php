<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Controller\Core\Migrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200111164416 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->connection->executeQuery(Migrations::buildSqlExecutedIfColumnDoesNotExist('currency', 'my_payment_owed', 'ALTER TABLE my_payment_owed ADD currency VARCHAR(255) NOT NULL'));

        $this->addSql(Migrations::getSuccessInformationSql());
    }

    public function down(Schema $schema) : void
    {
        $this->connection->executeQuery('ALTER TABLE my_payment_owed DROP currency');
    }
}
