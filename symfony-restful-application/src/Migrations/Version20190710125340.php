<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190710125340 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE point (point_id INT AUTO_INCREMENT NOT NULL, point_type_id INT DEFAULT NULL, point_name VARCHAR(255) NOT NULL, point_description VARCHAR(4000) DEFAULT NULL, point_longitude NUMERIC(11, 8) NOT NULL, point_latitude NUMERIC(10, 8) NOT NULL, point_city VARCHAR(150) DEFAULT NULL, INDEX IDX_B7A5F3247298755F (point_type_id), PRIMARY KEY(point_id)) DEFAULT CHARACTER SET cp1251 COLLATE cp1251_general_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE point_type (point_type_id INT AUTO_INCREMENT NOT NULL, point_type_name VARCHAR(150) NOT NULL, point_type_description VARCHAR(500) DEFAULT NULL, PRIMARY KEY(point_type_id)) DEFAULT CHARACTER SET cp1251 COLLATE cp1251_general_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE point ADD CONSTRAINT FK_B7A5F3247298755F FOREIGN KEY (point_type_id) REFERENCES point_type (point_type_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE point DROP FOREIGN KEY FK_B7A5F3247298755F');
        $this->addSql('DROP TABLE point');
        $this->addSql('DROP TABLE point_type');
    }
}
