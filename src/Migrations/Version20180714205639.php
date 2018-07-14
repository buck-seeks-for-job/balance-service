<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180714205639 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql("INSERT INTO accounts (type, name, created_at, currency, owner_id) VALUES ('nominal', 'bank_RUB', NOW(), 'RUB', 'bank');");
    }

    public function down(Schema $schema) : void
    {
        $this->addSql("DELETE FROM accounts WHERE type = 'nominal' AND owner_id = 'bank' AND currency = 'RUB';");
    }
}
