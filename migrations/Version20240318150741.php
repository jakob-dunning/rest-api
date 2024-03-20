<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240318150741 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE shopping_cart (id UUID NOT NULL, expires_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN shopping_cart.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE tablets_in_shoppingcarts (shoppingcart_id UUID NOT NULL, tablet_id UUID NOT NULL, PRIMARY KEY(shoppingcart_id, tablet_id))');
        $this->addSql('CREATE INDEX IDX_ACBA46E1685930AE ON tablets_in_shoppingcarts (shoppingcart_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ACBA46E11897676B ON tablets_in_shoppingcarts (tablet_id)');
        $this->addSql('COMMENT ON COLUMN tablets_in_shoppingcarts.shoppingcart_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN tablets_in_shoppingcarts.tablet_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE tablets_in_shoppingcarts ADD CONSTRAINT FK_ACBA46E1685930AE FOREIGN KEY (shoppingcart_id) REFERENCES shopping_cart (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tablets_in_shoppingcarts ADD CONSTRAINT FK_ACBA46E11897676B FOREIGN KEY (tablet_id) REFERENCES tablet (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tablets_in_shoppingcarts DROP CONSTRAINT FK_ACBA46E1685930AE');
        $this->addSql('ALTER TABLE tablets_in_shoppingcarts DROP CONSTRAINT FK_ACBA46E11897676B');
        $this->addSql('DROP TABLE shopping_cart');
        $this->addSql('DROP TABLE tablets_in_shoppingcarts');
    }
}
