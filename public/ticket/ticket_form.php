<?php

require_once __DIR__ . '/../../src/helpers.php';

$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Defaults für ein neues Ticket
$ticket = [
    'kurztitel'          => '',
    'quelle_stakeholder' => '',
    'prioritaet'         => 'mittel',
    'aufwand_pt'         => '',
    'bedingung'          => '',
    'verbindlichkeit'    => 'muss',
    'system_name'        => 'das System',
    'funktyp'            => 'benutzerinteraktion',
    'akteur'             => '',
    'objekt'             => '',
    'prozesswort'        => '',
];

if ($ticket_id !== null) {
    $abfrage = db()->prepare('SELECT * FROM tickets WHERE id = ?');
    $abfrage->execute([$ticket_id]);
    $vorhandenes_ticket = $abfrage->fetch();
    if (!$vorhandenes_ticket) {
        http_response_code(404);
        exit('Ticket nicht gefunden.');
    }
    $ticket = array_merge($ticket, $vorhandenes_ticket);
}

// Alle anderen Tickets für die Verknüpfungsauswahl
$abfrage = db()->prepare('SELECT id, kurztitel FROM tickets WHERE id <> ? ORDER BY id');
$abfrage->execute([$ticket_id ?? 0]);
$verknuepfbare_tickets = $abfrage->fetchAll();

// Bereits gesetzte Verweise (beim Bearbeiten)
$verweise = [];
if ($ticket_id !== null) {
    $abfrage = db()->prepare('SELECT verweist_auf FROM ticket_verweise WHERE ticket_id = ?');
    $abfrage->execute([$ticket_id]);
    $verweise = array_map('intval', array_column($abfrage->fetchAll(), 'verweist_auf'));
}

$fehlermeldungen = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ausgewählte Verweise übernehmen – nur IDs, die wirklich existieren
    $gueltige_ids = array_map('intval', array_column($verknuepfbare_tickets, 'id'));
    $verweise = array_values(array_intersect(
        array_map('intval', (array)($_POST['verweise'] ?? [])),
        $gueltige_ids
    ));

    foreach (array_keys($ticket) as $feld) {
        if (isset($_POST[$feld])) {
            $ticket[$feld] = trim($_POST[$feld]);
        }
    }

    if ($ticket['kurztitel'] === '') {
        $fehlermeldungen[] = 'Kurztitel darf nicht leer sein.';
    }
    if ($ticket['objekt'] === '') {
        $fehlermeldungen[] = 'Objekt darf nicht leer sein.';
    }
    if ($ticket['prozesswort'] === '') {
        $fehlermeldungen[] = 'Prozesswort darf nicht leer sein.';
    }
    if (!isset(PRIORITAETEN[$ticket['prioritaet']])) {
        $fehlermeldungen[] = 'Ungültige Priorität.';
    }
    if (!isset(VERBINDLICHKEITEN[$ticket['verbindlichkeit']])) {
        $fehlermeldungen[] = 'Ungültige Verbindlichkeit.';
    }
    if (!isset(FUNKTYPEN[$ticket['funktyp']])) {
        $fehlermeldungen[] = 'Ungültiger Funktionalitätstyp.';
    }

    $aufwand_pt = null;
    if ($ticket['aufwand_pt'] !== '') {
        $aufwand_pt = str_replace(',', '.', $ticket['aufwand_pt']);
        if (!is_numeric($aufwand_pt) || (float)$aufwand_pt < 0) {
            $fehlermeldungen[] = 'Personalaufwand muss eine Zahl ≥ 0 sein (in PT).';
        }
    }

    if (!$fehlermeldungen) {
        $spalten_werte = [
            $ticket['kurztitel'],
            $ticket['quelle_stakeholder'] !== '' ? $ticket['quelle_stakeholder'] : null,
            $ticket['prioritaet'],
            $aufwand_pt,
            $ticket['bedingung'] !== '' ? $ticket['bedingung'] : null,
            $ticket['verbindlichkeit'],
            $ticket['system_name'] !== '' ? $ticket['system_name'] : 'das System',
            $ticket['funktyp'],
            $ticket['akteur'] !== '' ? $ticket['akteur'] : null,
            $ticket['objekt'],
            $ticket['prozesswort'],
        ];

        if ($ticket_id === null) {
            db()->prepare(
                'INSERT INTO tickets (kurztitel, quelle_stakeholder, prioritaet, aufwand_pt,
                                      bedingung, verbindlichkeit, system_name, funktyp, akteur, objekt, prozesswort)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute($spalten_werte);
            $ticket_id = (int)db()->lastInsertId();
        } else {
            db()->prepare(
                'UPDATE tickets
                 SET kurztitel = ?, quelle_stakeholder = ?, prioritaet = ?, aufwand_pt = ?,
                     bedingung = ?, verbindlichkeit = ?, system_name = ?, funktyp = ?,
                     akteur = ?, objekt = ?, prozesswort = ?
                 WHERE id = ?'
            )->execute([...$spalten_werte, $ticket_id]);
        }

        // Verweise neu setzen (alte entfernen, ausgewählte eintragen)
        db()->prepare('DELETE FROM ticket_verweise WHERE ticket_id = ?')->execute([$ticket_id]);
        $verweis_einfuegen = db()->prepare('INSERT INTO ticket_verweise (ticket_id, verweist_auf) VALUES (?, ?)');
        foreach ($verweise as $verweis_id) {
            $verweis_einfuegen->execute([$ticket_id, $verweis_id]);
        }

        redirect('ticket_detail.php?id=' . $ticket_id);
    }
}

$titel = $ticket_id === null ? 'Neues Ticket' : 'Ticket ' . ticket_nr($ticket_id) . ' bearbeiten';
require __DIR__ . '/../../src/partials/header.php';
?>
<h1><?= ($titel) ?></h1>

<?php if ($fehlermeldungen): ?>
    <div class="fehlerbox">
        <ul>
            <?php foreach ($fehlermeldungen as $fehlermeldung): ?><li><?= ($fehlermeldung) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" class="formular" id="ticket-form">
    <fieldset>
        <legend>Allgemein</legend>
        <label>Kurztitel *
            <input type="text" name="kurztitel" maxlength="150" required value="<?= ($ticket['kurztitel']) ?>">
        </label>
        <label>Quelle / Stakeholder
            <input type="text" name="quelle_stakeholder" maxlength="150" value="<?= ($ticket['quelle_stakeholder']) ?>">
        </label>
        <div class="feld-reihe">
            <label>Priorität
                <select name="prioritaet">
                    <?php foreach (PRIORITAETEN as $wert => $label): ?>
                        <option value="<?= $wert ?>" <?= $ticket['prioritaet'] === $wert ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Aktuell geplanter Personalaufwand (PT)
                <input type="text" name="aufwand_pt" inputmode="decimal" placeholder="z. B. 2,25" value="<?= (str_replace('.', ',', (string)$ticket['aufwand_pt'])) ?>">
            </label>
        </div>
    </fieldset>

    <fieldset>
        <legend>Anforderung – Schablone nach Rupp</legend>
        <label>Bedingung <span class="hinweis">(optional – „Wann? Unter welcher Bedingung?“)</span>
            <input type="text" name="bedingung" maxlength="255" placeholder="z. B. Sobald ein Nutzer angemeldet ist" value="<?= ($ticket['bedingung']) ?>">
        </label>
        <div class="feld-reihe">
            <label>Verbindlichkeit
                <select name="verbindlichkeit">
                    <?php foreach (VERBINDLICHKEITEN as $wert => $label): ?>
                        <option value="<?= $wert ?>" <?= $ticket['verbindlichkeit'] === $wert ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>System
                <input type="text" name="system_name" maxlength="100" value="<?= ($ticket['system_name']) ?>">
            </label>
        </div>
        <label>Art der Funktionalität
            <select name="funktyp" id="funktyp">
                <?php foreach (FUNKTYPEN as $wert => $label): ?>
                    <option value="<?= $wert ?>" <?= $ticket['funktyp'] === $wert ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label id="akteur-feld">Wem? <span class="hinweis">(nur bei Benutzerinteraktion)</span>
            <input type="text" name="akteur" maxlength="100" placeholder="dem Nutzer" value="<?= ($ticket['akteur']) ?>">
        </label>
        <div class="feld-reihe">
            <label>Objekt *
                <input type="text" name="objekt" maxlength="150" required placeholder="z. B. die Bewerbungsliste" value="<?= ($ticket['objekt']) ?>">
            </label>
            <label>Prozesswort * <span class="hinweis">(Infinitiv)</span>
                <input type="text" name="prozesswort" maxlength="100" required placeholder="z. B. filtern" value="<?= ($ticket['prozesswort']) ?>">
            </label>
        </div>
        <div class="vorschau">
            <strong>Vorschau:</strong>
            <p id="rupp-vorschau"><?= (rupp_satz($ticket)) ?></p>
        </div>
    </fieldset>

    <fieldset>
        <legend>Verknüpfte Tickets <span class="hinweis">(dieses Ticket verweist auf …)</span></legend>
        <?php if (!$verknuepfbare_tickets): ?>
            <p class="hinweis">Noch keine anderen Tickets vorhanden.</p>
        <?php endif; ?>
        <div class="verweis-liste">
            <?php foreach ($verknuepfbare_tickets as $anderes_ticket): ?>
                <label class="verweis-option">
                    <input type="checkbox" name="verweise[]" value="<?= $anderes_ticket['id'] ?>"
                        <?= in_array((int)$anderes_ticket['id'], $verweise, true) ? 'checked' : '' ?>>
                    <span class="ticket-nr"><?= ticket_nr((int)$anderes_ticket['id']) ?></span>
                    <?= $anderes_ticket['kurztitel'] ?>
                </label>
            <?php endforeach; ?>
        </div>
    </fieldset>

    <div class="aktionen">
        <button type="submit" class="btn btn-primary">Speichern</button>
        <a class="btn" href="<?= $ticket_id !== null ? 'ticket_detail.php?id=' . $ticket_id : 'index.php' ?>">Abbrechen</a>
    </div>
</form>
<script src="js/ticket_form.js"></script>
<?php require __DIR__ . '/../../src/partials/footer.php'; ?>
