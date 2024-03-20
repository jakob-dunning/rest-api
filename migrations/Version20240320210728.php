<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240320210728 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tablets_in_shoppingcarts DROP CONSTRAINT FK_ACBA46E11897676B');
        $this->addSql('ALTER TABLE tablets_in_shoppingcarts DROP CONSTRAINT FK_ACBA46E1685930AE');
        $this->addSql('ALTER TABLE tablets_in_shoppingcarts ADD CONSTRAINT FK_ACBA46E11897676B FOREIGN KEY (tablet_id) REFERENCES tablet (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tablets_in_shoppingcarts ADD CONSTRAINT FK_ACBA46E1685930AE FOREIGN KEY (shoppingcart_id) REFERENCES shopping_cart (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tablets_in_shoppingcarts DROP CONSTRAINT fk_acba46e1685930ae');
        $this->addSql('ALTER TABLE tablets_in_shoppingcarts DROP CONSTRAINT fk_acba46e11897676b');
        $this->addSql('ALTER TABLE tablets_in_shoppingcarts ADD CONSTRAINT fk_acba46e1685930ae FOREIGN KEY (shoppingcart_id) REFERENCES shopping_cart (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tablets_in_shoppingcarts ADD CONSTRAINT fk_acba46e11897676b FOREIGN KEY (tablet_id) REFERENCES tablet (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
