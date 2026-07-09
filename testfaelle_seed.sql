-- Testfälle zu den 10 Anforderungen (Referenz: ticket_id, Tickets T-001–T-010).
-- Ersetzt vorhandene Testfälle vollständig; beliebig oft ausführbar:
--   mysql -h <host> -u <user> -p <datenbank> < testfaelle_seed.sql

DELETE FROM testfaelle;
ALTER TABLE testfaelle AUTO_INCREMENT = 1;

-- Anforderung 1 – Filtern über Applicant-ID (Ticket T-002)
INSERT INTO testfaelle (ticket_id, vorbedingung, fehlersituation, trigger_eingabe, erwartetes_ergebnis) VALUES
(2, 'Mindestens ein Bewerber mit bekannter Applicant-ID ist in der Datenbank vorhanden', NULL,
 '1. System aufrufen\n2. Gültige Applicant-ID in das Filterfeld eingeben\n3. Filter anwenden',
 'Das System zeigt genau den Bewerber mit der eingegebenen Applicant-ID an'),
(2, 'Datenbank enthält Bewerber', 'Eingegebene Applicant-ID existiert nicht',
 '1. System aufrufen\n2. Nicht existierende Applicant-ID eingeben\n3. Filter anwenden',
 'Das System zeigt keine Treffer und gibt einen entsprechenden Hinweis aus');

-- Anforderung 2 – Application Status über Radio Buttons filtern (Ticket T-003)
INSERT INTO testfaelle (ticket_id, vorbedingung, fehlersituation, trigger_eingabe, erwartetes_ergebnis) VALUES
(3, 'Bewerber mit unterschiedlichen Status sind vorhanden', NULL,
 '1. System aufrufen\n2. Radio Button eines Status (z. B. „In Review") auswählen\n3. Filter anwenden',
 'Das System zeigt ausschließlich Bewerber mit dem gewählten Status an'),
(3, 'Ein Statusfilter ist bereits aktiv', NULL,
 '1. Anderen Radio Button (z. B. „Rejected") auswählen',
 'Das System aktualisiert die Anzeige und zeigt nur Bewerber des neu gewählten Status; der vorherige Status ist nicht mehr aktiv (Radio Buttons schließen sich gegenseitig aus)');

-- Anforderung 3 – Erreichbarkeit außerhalb des internen Netzwerks (Ticket T-004)
INSERT INTO testfaelle (ticket_id, vorbedingung, fehlersituation, trigger_eingabe, erwartetes_ergebnis) VALUES
(4, 'System ist deployt und läuft', NULL,
 '1. Gerät verwenden, das nicht im internen Unternehmensnetzwerk ist (z. B. mobiles Netz)\n2. System-URL aufrufen',
 'Das System ist erreichbar und die Startseite wird geladen'),
(4, 'System ist deployt und läuft', NULL,
 '1. Externes WLAN/Home-Office-Verbindung nutzen\n2. System-URL aufrufen',
 'Das System ist erreichbar und funktioniert wie im internen Netzwerk');

-- Anforderung 4 – Initial nur Filtermöglichkeiten anzeigen (Ticket T-005)
INSERT INTO testfaelle (ticket_id, vorbedingung, fehlersituation, trigger_eingabe, erwartetes_ergebnis) VALUES
(5, 'Personalleiter ist berechtigt', NULL,
 '1. Personalleiter ruft das System zum ersten Mal / neu auf',
 'Das System zeigt ausschließlich die Filtermöglichkeiten an, keine Bewerberdaten/Tabelle'),
(5, 'Datenbank enthält Bewerber', NULL,
 '1. System initial aufrufen\n2. Prüfen, ob eine Ergebnistabelle sichtbar ist',
 'Es wird keine Tabelle und keine Bewerberliste angezeigt, solange kein Filter angewendet wurde');

-- Anforderung 5 – Tabellarische Darstellung der Inhalte (Ticket T-006)
INSERT INTO testfaelle (ticket_id, vorbedingung, fehlersituation, trigger_eingabe, erwartetes_ergebnis) VALUES
(6, 'Mehrere Bewerber sind vorhanden', NULL,
 '1. Filter anwenden, der mehrere Treffer liefert',
 'Das System gibt die Ergebnisse in einer tabellarischen Darstellung (Zeilen/Spalten) aus'),
(6, 'Genau ein Bewerber passt auf den Filter', NULL,
 '1. Filter anwenden, der einen Treffer liefert',
 'Auch ein einzelner Treffer wird korrekt als Tabellenzeile dargestellt');

-- Anforderung 6 – Mehrere Filter gleichzeitig verwenden (Ticket T-007)
INSERT INTO testfaelle (ticket_id, vorbedingung, fehlersituation, trigger_eingabe, erwartetes_ergebnis) VALUES
(7, 'Bewerber mit passender ID und passendem Status vorhanden', NULL,
 '1. Applicant-ID-Filter setzen\n2. Zusätzlich Status-Filter setzen\n3. Filter anwenden',
 'Das System zeigt nur Bewerber, die beide Filterkriterien erfüllen (Schnittmenge)'),
(7, 'Datenbank enthält Bewerber', 'Kombination der Filter liefert keine Übereinstimmung',
 '1. ID-Filter und Status-Filter kombinieren, die auf keinen Bewerber gleichzeitig zutreffen\n2. Filter anwenden',
 'Das System zeigt keine Treffer und weist auf fehlende Ergebnisse hin');

-- Anforderung 7 – Ausgewählte Filter wieder entfernen (Ticket T-008)
INSERT INTO testfaelle (ticket_id, vorbedingung, fehlersituation, trigger_eingabe, erwartetes_ergebnis) VALUES
(8, 'Mindestens ein Filter ist aktiv gesetzt', NULL,
 '1. Einen aktiven Filter über die Entfernen-Funktion löschen',
 'Der Filter wird entfernt und die Ergebnisliste aktualisiert sich entsprechend'),
(8, 'Mehrere Filter sind aktiv', NULL,
 '1. Alle aktiven Filter nacheinander entfernen',
 'Nach Entfernen aller Filter befindet sich das System wieder im ungefilterten Ausgangszustand');

-- Anforderung 8 – Speicherung aller Bewerbungen in zentraler Datenbank (Ticket T-009)
INSERT INTO testfaelle (ticket_id, vorbedingung, fehlersituation, trigger_eingabe, erwartetes_ergebnis) VALUES
(9, 'System ist bedienbar, Datenbank erreichbar', NULL,
 '1. Neue Bewerbung erfassen/eingehen lassen\n2. In der zentralen Datenbank prüfen',
 'Die Bewerbung ist in der zentralen Datenbank gespeichert und abrufbar'),
(9, 'Eine Bewerbung wurde gespeichert', NULL,
 '1. System neu laden / neu aufrufen\n2. Nach der Bewerbung filtern',
 'Die zuvor gespeicherte Bewerbung ist weiterhin vorhanden (Daten bleiben persistent)');

-- Anforderung 9 – Hinweis bei fehlenden Filter-Treffern (Ticket T-010)
INSERT INTO testfaelle (ticket_id, vorbedingung, fehlersituation, trigger_eingabe, erwartetes_ergebnis) VALUES
(10, 'Datenbank enthält Bewerber', 'Kein Bewerber entspricht dem gewählten Filter',
 '1. Filter setzen, der garantiert keinen Treffer liefert\n2. Filter anwenden',
 'Das System informiert den Nutzer, dass keine Daten gefunden wurden'),
(10, 'Datenbank enthält Bewerber', 'Statusfilter ohne zugehörige Bewerber ausgewählt',
 '1. Status auswählen, für den kein Bewerber existiert\n2. Filter anwenden',
 'Das System zeigt eine verständliche „Keine Daten gefunden"-Meldung statt einer leeren Tabelle ohne Hinweis');

-- Anforderung 10 – Verbindung zur Datenbank und Abruf (Ticket T-001)
INSERT INTO testfaelle (ticket_id, vorbedingung, fehlersituation, trigger_eingabe, erwartetes_ergebnis) VALUES
(1, 'Datenbank ist erreichbar und enthält Daten', NULL,
 '1. System aufrufen\n2. Filter anwenden, der Daten liefert',
 'Das System ruft die Daten erfolgreich aus der Datenbank ab und zeigt sie an'),
(1, 'System ist gestartet', 'Datenbank ist nicht erreichbar / Verbindung schlägt fehl',
 '1. Datenbankverbindung unterbrechen\n2. Filter anwenden / Daten abrufen',
 'Das System fängt den Fehler ab und gibt eine verständliche Fehlermeldung aus, statt abzustürzen');
