<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230205165609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE "Movies_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "Users_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "Votes_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "Movies" (id INT NOT NULL, owner_id INT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, photo_path VARCHAR(255) DEFAULT NULL, likes INT DEFAULT NULL, hates INT DEFAULT NULL, date_published TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C1B2E8067E3C61F9 ON "Movies" (owner_id)');
        $this->addSql('COMMENT ON COLUMN "Movies".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "Users" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D5428AEDE7927C74 ON "Users" (email)');
        $this->addSql('CREATE TABLE "Votes" (id INT NOT NULL, movie_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_904A55CB8F93B6FC ON "Votes" (movie_id)');
        $this->addSql('COMMENT ON COLUMN "Votes".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE "Movies" ADD CONSTRAINT FK_C1B2E8067E3C61F9 FOREIGN KEY (owner_id) REFERENCES "Users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "Votes" ADD CONSTRAINT FK_904A55CB8F93B6FC FOREIGN KEY (movie_id) REFERENCES "Movies" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE "Movies_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE "Users_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE "Votes_id_seq" CASCADE');
        $this->addSql('ALTER TABLE "Movies" DROP CONSTRAINT FK_C1B2E8067E3C61F9');
        $this->addSql('ALTER TABLE "Votes" DROP CONSTRAINT FK_904A55CB8F93B6FC');
        $this->addSql('DROP TABLE "Movies"');
        $this->addSql('DROP TABLE "Users"');
        $this->addSql('DROP TABLE "Votes"');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
