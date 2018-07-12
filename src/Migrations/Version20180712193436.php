<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180712193436 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql("CREATE SEQUENCE accounts_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $this->addSql("CREATE SEQUENCE entries_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $this->addSql("
            CREATE TABLE accounts (
              id         INT                            NOT NULL DEFAULT nextval('accounts_id_seq'),
              created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              currency   VARCHAR(3)                     NOT NULL,
                type       VARCHAR(10)                    NOT NULL,
                owner_id   VARCHAR(20) DEFAULT NULL,
                name       VARCHAR(32) DEFAULT NULL,
            PRIMARY KEY (id)
        );");
        $this->addSql("
            CREATE UNIQUE INDEX unq_type_currency
              ON accounts (type, owner_id, currency)
              WHERE type = 'nominal';
        ");
        $this->addSql("
            CREATE TABLE entries (
              id              BIGINT                         NOT NULL DEFAULT nextval('entries_id_seq'),
              transaction_id  UUID DEFAULT NULL,
              created_at      TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              account_id      INT                            NOT NULL,
              amount_amount   BIGINT                         NOT NULL,
              amount_currency VARCHAR(3)                     NOT NULL,
            PRIMARY KEY (id)
            );
        ");
        $this->addSql("
            CREATE INDEX idx_entries_transaction_id
              ON entries (transaction_id);
        ");
        $this->addSql("
            CREATE TABLE transactions (
              id              UUID                           NOT NULL,
              type            VARCHAR(16)                    NOT NULL,
              created_at      TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              amount_amount   BIGINT                         NOT NULL,
              amount_currency VARCHAR(3)                     NOT NULL,
            PRIMARY KEY (id)
            );
        ");
        $this->addSql("
            ALTER TABLE entries
              ADD CONSTRAINT fk_entries_transactions FOREIGN KEY (transaction_id) REFERENCES transactions (id)
              NOT DEFERRABLE INITIALLY IMMEDIATE;
        ");
    }

    public function down(Schema $schema) : void
    {
        //was too lazy to create useless down migrations
    }
}
