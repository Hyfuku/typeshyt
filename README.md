# typeshyt – Ticket- & Bewerbungssystem

Uni-Projekt: Ticketsystem mit Anforderungen nach der **Schablone von Rupp**, verknüpfbaren **Testfällen** und Kanban-Board (Ready / Doing / Finish) mit Drag & Drop. Milestone 2: Bewerbungs-Tracker mit filterbarer Tabelle.

**Stack:** PHP 8 (ohne Framework) · MySQL (PDO) · Vanilla JS · läuft unter Apache oder dem PHP-Entwicklungsserver.

## Setup

```bash
# 1. Datenbank anlegen
mysql -uroot < schema.sql

# 2. Zugangsdaten prüfen/anpassen
#    → src/config.php

# 3. Entwicklungsserver starten
php -S localhost:8000 -t public
# → http://localhost:8000
```

Für Apache: `DocumentRoot` auf `public/` zeigen lassen.

## Test über Apache (Docker + Colima)

```bash
colima start          # falls nicht schon aktiv
docker-compose up -d --build
# → http://localhost:8081
```

Startet zwei Container: `web` (php:8.4-apache, DocumentRoot auf `public/`) und `db` (MySQL 9, importiert `schema.sql` beim ersten Start automatisch). Die DB-Zugangsdaten kommen per Umgebungsvariablen aus `docker-compose.yml`; ohne diese Variablen nutzt `src/config.php` die lokalen Defaults (Homebrew-MySQL). Stoppen mit `docker-compose down` (Daten bleiben im Volume `db_daten` erhalten).

## Datenbank einsehen (Docker-MySQL)

Die Container-DB ist auf dem Host unter Port **3307** erreichbar (3306 gehört dem lokalen Homebrew-MySQL — nicht verwechseln!).

**Variante 1 – mysql-Client vom Mac aus:**

```bash
mysql -h127.0.0.1 -P3307 -utypeshyt -ptypeshyt typeshyt

# Beispiele:
mysql> SHOW TABLES;
mysql> SELECT * FROM tickets;
mysql> SELECT * FROM testfaelle WHERE ticket_id = 1;
```

**Variante 2 – direkt im Container (ohne Portweiterleitung):**

```bash
docker-compose exec db mysql -utypeshyt -ptypeshyt typeshyt
```

**Variante 3 – Sequel Ace:**

```bash
./bin/sqlace
```

Startet bei Bedarf den DB-Container und öffnet die Verbindung direkt in Sequel Ace (Host `127.0.0.1`, Port `3307`, User/Passwort/DB `typeshyt`). Beim allerersten Mal fragt Sequel Ace nach dem Passwort (`typeshyt`) – mit „In Schlüsselbund sichern“ merkt es sich das dauerhaft.

**Variante 4 – PhpStorm:** *Database-Toolwindow → + → Data Source → MySQL*, dann Host `127.0.0.1`, Port `3307`, User/Passwort/Datenbank jeweils `typeshyt`. (Root-Passwort, falls nötig: `root`.)

## Deployment (goserver Shared Hosting)

Docroot: `/home/www/alexanderkhuu.de` auf `web23@s71.goserver.host`. Die `.htaccess` im Projektstamm übernimmt Basic Auth, das Rewrite nach `public/` und sperrt `src/`, `scss/`, SQL-/Docker-Dateien.

**Einmalige Einrichtung auf dem Server:**

```bash
ssh web23@s71.goserver.host

# 1. PHP-Version prüfen (Code braucht >= 8.1; sonst im goserver-Panel umstellen)
php -v

# 2. Deploy-Key erzeugen, Public Key bei GitHub als read-only Deploy Key eintragen
ssh-keygen -t ed25519 -N '' -f ~/.ssh/id_ed25519
cat ~/.ssh/id_ed25519.pub   # → GitHub → Repo → Settings → Deploy keys

# 3. Klonen (Docroot muss leer sein)
cd /home/www/alexanderkhuu.de
git clone git@github.com:<github-user>/typeshyt.git .

# 4. DB-Zugangsdaten hinterlegen (nicht in Git!)
cp src/config.local.php.example src/config.local.php
nano src/config.local.php     # echtes Passwort eintragen

# 5. Basic-Auth-Nutzer anlegen (Pfad muss zu AuthUserFile in .htaccess passen)
htpasswd -c /home/www/.htpasswd <benutzername>

# 6. Schema + Testdaten importieren
#    (CREATE DATABASE/USE aus schema.sql überspringen – Shared Hosting
#     erlaubt kein Anlegen von Datenbanken)
sed '1,/^USE typeshyt;$/d' schema.sql | mysql -h s71.goserver.host -u web23_2 -p web23_db2
mysql -h s71.goserver.host -u web23_2 -p web23_db2 < testdaten.sql
```

**Updates deployen:** lokal `git push`, dann auf dem Server:

```bash
ssh web23@s71.goserver.host 'cd /home/www/alexanderkhuu.de && git pull'
```

## Styling (SCSS)

Die Filterleiste ist in `scss/filter.scss` gestylt (Pill-Controls, UND-Verbindungslinien, Geist-Font aus `public/fonts/`). Nach Änderungen kompilieren:

```bash
sass scss/filter.scss public/css/filter.css --no-source-map
# oder während der Entwicklung:
sass --watch scss/filter.scss public/css/filter.css
```

## Struktur

```
public/                  ← DocumentRoot
  index.php              ← leitet zum Board weiter
  css/style.css          ← gemeinsames Stylesheet
  ticket/                ← Ticket-System
    index.php            ← Kanban-Board (Drag & Drop)
    ticket_form.php      ← Ticket anlegen/bearbeiten (Rupp-Schablone mit Live-Vorschau)
    ticket_detail.php    ← Ticketdetails + Testfälle
    testfall_form.php    ← Testfall anlegen/bearbeiten
    api/status.php       ← Statuswechsel per Drag & Drop (JSON)
    js/                  ← Board- und Formular-JS
  application/           ← Bewerbungs-Tracker (Milestone 2)
src/
  config.php         ← DB-Zugangsdaten
  db.php             ← PDO-Verbindung
  helpers.php        ← Rupp-Satzgenerator, Konstanten, Escaping
schema.sql           ← MySQL-Schema (tickets, testfaelle, bewerbungen)
```
