<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180712184728 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql("CREATE EXTENSION IF NOT EXISTS \"uuid-ossp\";");
    }

    public function down(Schema $schema) : void
    {

    }
}
