-- Politur der 10 Anforderungen für die Abgabe:
-- vollständige Rupp-Sätze, Tippfehler, fehlende Quellen/PT, Verknüpfungen.

UPDATE tickets SET
    funktyp = 'schnittstelle',
    akteur = NULL,
    objekt = 'die Bewerbungsdaten aus der zentralen Datenbank',
    prozesswort = 'laden'
WHERE id = 1;

UPDATE tickets SET objekt = 'auch außerhalb des internen Unternehmensnetzwerks'
WHERE id = 4;

UPDATE tickets SET
    quelle_stakeholder = 'Personalleiter',
    aufwand_pt = 0.25
WHERE id = 5;

UPDATE tickets SET
    quelle_stakeholder = 'Personalleiter',
    aufwand_pt = 0.5
WHERE id = 6;

UPDATE tickets SET
    kurztitel = 'Filterkombination',
    quelle_stakeholder = 'Personalleiter',
    aufwand_pt = 0.5,
    objekt = 'mehrere Filter gleichzeitig'
WHERE id = 7;

UPDATE tickets SET
    quelle_stakeholder = 'Personalleiter',
    aufwand_pt = 0.25,
    bedingung = 'Sobald der Nutzer einen Filter ausgewählt hat',
    akteur = 'dem Nutzer',
    objekt = 'die gesetzten Filter wieder',
    prozesswort = 'entfernen'
WHERE id = 8;

UPDATE tickets SET
    quelle_stakeholder = 'Geschäftsleitung des Unternehmens U',
    aufwand_pt = 0.75,
    bedingung = NULL
WHERE id = 9;

UPDATE tickets SET
    quelle_stakeholder = 'Personalleiter',
    aufwand_pt = 0.25,
    bedingung = 'Falls bei der Filterung kein Bewerber zugeordnet werden kann',
    objekt = 'einen Hinweis auf fehlende Treffer',
    prozesswort = 'anzeigen'
WHERE id = 10;

-- Verknüpfungen: gerichtete Kanten „beeinflusst" (4→9 = möglicher Widerspruch)
DELETE FROM ticket_verweise;
INSERT INTO ticket_verweise (ticket_id, verweist_auf) VALUES
(2, 7), (3, 7),          -- ID-/Status-Filter beeinflussen die Filterkombination
(2, 10), (3, 10),        -- ... und den Kein-Treffer-Hinweis
(5, 6),                  -- initial nur Filter beeinflusst die Tabellendarstellung
(8, 5),                  -- Filter entfernen führt zurück in den Ausgangszustand
(9, 1),                  -- zentrale Speicherung beeinflusst den Datenabruf
(4, 9);                  -- externe Erreichbarkeit vs. zentrale Daten (Datenschutz!)
