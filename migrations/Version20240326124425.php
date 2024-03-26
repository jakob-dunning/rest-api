<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240326124425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE product (id UUID NOT NULL, type VARCHAR(255) NOT NULL, manufacturer VARCHAR(255) NOT NULL, model VARCHAR(255) NOT NULL, price INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN product.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE shopping_cart_product (shopping_cart_id UUID NOT NULL, product_id UUID NOT NULL, PRIMARY KEY(shopping_cart_id, product_id))');
        $this->addSql('CREATE INDEX IDX_FA1F5E6C45F80CD ON shopping_cart_product (shopping_cart_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FA1F5E6C4584665A ON shopping_cart_product (product_id)');
        $this->addSql('COMMENT ON COLUMN shopping_cart_product.shopping_cart_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN shopping_cart_product.product_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE shopping_cart_product ADD CONSTRAINT FK_FA1F5E6C45F80CD FOREIGN KEY (shopping_cart_id) REFERENCES shopping_cart (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shopping_cart_product ADD CONSTRAINT FK_FA1F5E6C4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tablets_in_shoppingcarts DROP CONSTRAINT fk_acba46e11897676b');
        $this->addSql('ALTER TABLE tablets_in_shoppingcarts DROP CONSTRAINT fk_acba46e1685930ae');
        $this->addSql('DROP TABLE tablets_in_shoppingcarts');
        $this->addSql('DROP TABLE tablet');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tablets_in_shoppingcarts (shoppingcart_id UUID NOT NULL, tablet_id UUID NOT NULL, PRIMARY KEY(shoppingcart_id, tablet_id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_acba46e11897676b ON tablets_in_shoppingcarts (tablet_id)');
        $this->addSql('CREATE INDEX idx_acba46e1685930ae ON tablets_in_shoppingcarts (shoppingcart_id)');
        $this->addSql('COMMENT ON COLUMN tablets_in_shoppingcarts.shoppingcart_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN tablets_in_shoppingcarts.tablet_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE tablet (id UUID NOT NULL, manufacturer VARCHAR(255) NOT NULL, model VARCHAR(255) NOT NULL, price INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN tablet.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE tablets_in_shoppingcarts ADD CONSTRAINT fk_acba46e11897676b FOREIGN KEY (tablet_id) REFERENCES tablet (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tablets_in_shoppingcarts ADD CONSTRAINT fk_acba46e1685930ae FOREIGN KEY (shoppingcart_id) REFERENCES shopping_cart (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shopping_cart_product DROP CONSTRAINT FK_FA1F5E6C45F80CD');
        $this->addSql('ALTER TABLE shopping_cart_product DROP CONSTRAINT FK_FA1F5E6C4584665A');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE shopping_cart_product');
    }
}
