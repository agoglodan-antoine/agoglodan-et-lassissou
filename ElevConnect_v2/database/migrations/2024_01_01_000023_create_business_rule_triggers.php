<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Règles de gestion de premier niveau, portées en base par des triggers MySQL
 * (cf. ElevConnect_schema_bdd_v4.sql, section "TRIGGERS"). Ces règles sont
 * redondantes avec des contrôles applicatifs (Form Requests / Policies Laravel)
 * mais sont conservées en base comme filet de sécurité, conformément au mémoire.
 *
 * Note : ces triggers sont spécifiques à MySQL et sont ignorés silencieusement
 * si le SGBD ne supporte pas la syntaxe DELIMITER / SIGNAL (ex. SQLite en tests).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('
            CREATE TRIGGER trg_actualites_auteur_role BEFORE INSERT ON actualites
            FOR EACH ROW
            BEGIN
                DECLARE auteur_role VARCHAR(20);
                SELECT role INTO auteur_role FROM utilisateurs WHERE id_utilisateur = NEW.id_auteur;
                IF auteur_role = "acheteur" THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Un acheteur ne peut pas publier d\'actualité.";
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER trg_annonces_type_role BEFORE INSERT ON annonces
            FOR EACH ROW
            BEGIN
                DECLARE auteur_role VARCHAR(30);
                SELECT role INTO auteur_role FROM utilisateurs WHERE id_utilisateur = NEW.id_utilisateur;
                IF (NEW.type_annonce = "animal" AND auteur_role != "eleveur")
                   OR (NEW.type_annonce = "provende" AND auteur_role != "vendeur_provende")
                   OR (NEW.type_annonce = "accessoire" AND auteur_role != "vendeur_accessoire") THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Le type d\'annonce ne correspond pas au rôle de l\'utilisateur.";
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER trg_commandes_pas_auto_commande BEFORE INSERT ON commandes
            FOR EACH ROW
            BEGIN
                DECLARE proprietaire_annonce INT UNSIGNED;
                SELECT id_utilisateur INTO proprietaire_annonce FROM annonces WHERE id_annonce = NEW.id_annonce;
                IF proprietaire_annonce = NEW.id_acheteur THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Un utilisateur ne peut pas commander sa propre annonce.";
                END IF;
            END
        ');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS trg_actualites_auteur_role');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_annonces_type_role');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_commandes_pas_auto_commande');
    }
};
