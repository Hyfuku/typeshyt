<?php

// Erzeugt die Abgabe-Dokumentation (Aufgabe 1) als HTML aus der Datenbank.
// Aufruf:  php bin/dokument.php > docs/aufgabe1.html
//          textutil -convert docx docs/aufgabe1.html -o docs/Aufgabe1_Anforderungsspezifikation.docx

require_once __DIR__ . '/../src/helpers.php';

$tickets = db()->query('SELECT * FROM tickets ORDER BY id')->fetchAll();
$testfaelle = db()->query('SELECT * FROM testfaelle ORDER BY id')->fetchAll();
$verweise = db()->query('SELECT ticket_id, verweist_auf FROM ticket_verweise ORDER BY ticket_id, verweist_auf')->fetchAll();

function a_nr(int $id): string
{
    return sprintf('A-%03d', $id);
}

// Verknüpfungstexte je Ticket ("beeinflusst A-00X"; 4->9 als möglicher Widerspruch)
$verknuepfungen = [];
foreach ($verweise as $verweis) {
    $von = (int)$verweis['ticket_id'];
    $auf = (int)$verweis['verweist_auf'];
    $verknuepfungen[$von][] = ($von === 4 && $auf === 9)
        ? 'möglicher Widerspruch zu ' . a_nr($auf) . ' (Datenschutz bei externer Erreichbarkeit)'
        : 'beeinflusst ' . a_nr($auf);
}

$kopf_stil = 'background:#4472C4;color:#fff;text-align:left;padding:6px 8px;border:1px solid #8ea9db;';
$zellen_stil = 'padding:6px 8px;border:1px solid #b4c6e7;vertical-align:top;';
$streifen_stil = 'background:#D9E2F3;';

ob_start();
?>
<!DOCTYPE html>
<html lang="de">
<head><meta charset="utf-8"><title>Aufgabe 1 – Anforderungsspezifikation</title></head>
<body style="font-family:Calibri, Arial, sans-serif;font-size:11pt;">

<h1>Aufgabe 1 – Anforderungsspezifikation</h1>
<p>Funktionale Anforderungen nach der Anforderungsschablone von Rupp, inklusive Fehler- und
Grenzfällen. Jede Anforderung besitzt eine eindeutige ID (A-001 … A-010, entspricht den
Tickets T-001 … T-010 im Ticketsystem) sowie die Attribute Kurztitel, formulierte Anforderung,
Quelle/Stakeholder, Priorität, aktuell geplanter Personalaufwand und Verknüpfungen zu anderen
Anforderungen.</p>

<h2>Funktionale Anforderungen</h2>
<table style="border-collapse:collapse;width:100%;">
    <tr>
        <th style="<?= $kopf_stil ?>">ID</th>
        <th style="<?= $kopf_stil ?>">Kurztitel</th>
        <th style="<?= $kopf_stil ?>">Anforderung nach Rupp</th>
        <th style="<?= $kopf_stil ?>">Quelle / Stakeholder</th>
        <th style="<?= $kopf_stil ?>">Priorität</th>
        <th style="<?= $kopf_stil ?>">Aufwand</th>
        <th style="<?= $kopf_stil ?>">Verknüpfungen</th>
    </tr>
    <?php foreach ($tickets as $i => $ticket): ?>
    <tr<?= $i % 2 === 0 ? ' style="' . $streifen_stil . '"' : '' ?>>
        <td style="<?= $zellen_stil ?>"><b><?= a_nr((int)$ticket['id']) ?></b></td>
        <td style="<?= $zellen_stil ?>"><?= $ticket['kurztitel'] ?></td>
        <td style="<?= $zellen_stil ?>"><?= rupp_satz($ticket) ?></td>
        <td style="<?= $zellen_stil ?>"><?= $ticket['quelle_stakeholder'] ?? '–' ?></td>
        <td style="<?= $zellen_stil ?>"><?= PRIORITAETEN[$ticket['prioritaet']] ?></td>
        <td style="<?= $zellen_stil ?>"><?= pt_format($ticket['aufwand_pt']) ?? '–' ?></td>
        <td style="<?= $zellen_stil ?>"><?= isset($verknuepfungen[(int)$ticket['id']]) ? implode('; ', $verknuepfungen[(int)$ticket['id']]) : '–' ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<h2>Testfälle</h2>
<p>Zu jeder Anforderung existieren zwei Testfälle (Positiv-, Negativ- bzw. Grenzfälle).
Die Referenz-ID verweist auf die Anforderung aus der obigen Tabelle.</p>
<table style="border-collapse:collapse;width:100%;">
    <tr>
        <th style="<?= $kopf_stil ?>">Testfall-ID</th>
        <th style="<?= $kopf_stil ?>">Referenz auf Anforderungs-ID</th>
        <th style="<?= $kopf_stil ?>">Vorbedingung</th>
        <th style="<?= $kopf_stil ?>">Fehlersituation (optional)</th>
        <th style="<?= $kopf_stil ?>">Trigger / Stimuli / Eingabesequenz</th>
        <th style="<?= $kopf_stil ?>">Erwartete Ergebnisse / Ausgaben</th>
    </tr>
    <?php foreach ($testfaelle as $i => $testfall): ?>
    <tr<?= $i % 2 === 0 ? ' style="' . $streifen_stil . '"' : '' ?>>
        <td style="<?= $zellen_stil ?>"><b><?= tf_nr((int)$testfall['id']) ?></b></td>
        <td style="<?= $zellen_stil ?>"><?= a_nr((int)$testfall['ticket_id']) ?></td>
        <td style="<?= $zellen_stil ?>"><?= $testfall['vorbedingung'] !== null && $testfall['vorbedingung'] !== '' ? nl2br($testfall['vorbedingung']) : '–' ?></td>
        <td style="<?= $zellen_stil ?>"><?= $testfall['fehlersituation'] !== null && $testfall['fehlersituation'] !== '' ? nl2br($testfall['fehlersituation']) : '–' ?></td>
        <td style="<?= $zellen_stil ?>"><?= nl2br($testfall['trigger_eingabe']) ?></td>
        <td style="<?= $zellen_stil ?>"><?= nl2br($testfall['erwartetes_ergebnis']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
<?php
echo ob_get_clean();
