-- typeshyt – Ticket- & Bewerbungssystem
-- Import: mysql -uroot < schema.sql

CREATE DATABASE IF NOT EXISTS typeshyt
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE typeshyt;

CREATE TABLE IF NOT EXISTS tickets (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kurztitel          VARCHAR(150)  NOT NULL,
    quelle_stakeholder VARCHAR(150)  NULL,
    prioritaet         ENUM('hoch','mittel','niedrig') NOT NULL DEFAULT 'mittel',
    aufwand_pt         DECIMAL(5,2)  NULL,
    bedingung          VARCHAR(255)  NULL,
    verbindlichkeit    ENUM('muss','soll','wird') NOT NULL DEFAULT 'muss',
    system_name        VARCHAR(100)  NOT NULL DEFAULT 'das System',
    funktyp            ENUM('selbstaendig','benutzerinteraktion','schnittstelle') NOT NULL,
    akteur             VARCHAR(100)  NULL,
    objekt             VARCHAR(150)  NOT NULL,
    prozesswort        VARCHAR(100)  NOT NULL,

    status             ENUM('ready','doing','finish') NOT NULL DEFAULT 'ready',
    created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS testfaelle (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id           INT UNSIGNED NOT NULL,       -- Referenz-ID zum Ticket
    vorbedingung        TEXT NULL,
    fehlersituation     TEXT NULL,
    trigger_eingabe     TEXT NOT NULL,               -- Trigger / Eingabesequenz
    erwartetes_ergebnis TEXT NOT NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_testfall_ticket
        FOREIGN KEY (ticket_id) REFERENCES tickets (id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bewerbungen (
    bewerbungsnr         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bewerbernr      VARCHAR(150) NOT NULL,
    arbeitszeit   VARCHAR(150) NOT NULL,
    bearbeitungsstatus     ENUM('offen','angenommen','in Bearbeitung','abgelehnt','vollständig') NOT NULL DEFAULT 'offen'
) ENGINE=InnoDB;
