<?php

require_once __DIR__ . '/../../src/helpers.php';

$testfall_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$testfall = [
    'ticket_id'           => isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0,
    'vorbedingung'        => '',
    'fehlersituation'     => '',
    'trigger_eingabe'     => '',
    'erwartetes_ergebnis' => '',
];

if ($testfall_id !== null) {
    $abfrage = db()->prepare('SELECT * FROM testfaelle WHERE id = ?');
    $abfrage->execute([$testfall_id]);
    $vorhandener_testfall = $abfrage->fetch();
    if (!$vorhandener_testfall) {
        http_response_code(404);
        exit('Testfall nicht gefunden.');
    }
    $testfall = array_merge($testfall, $vorhandener_testfall);
}

$abfrage = db()->prepare('SELECT id, kurztitel FROM tickets WHERE id = ?');
$abfrage->execute([$testfall['ticket_id']]);
$ticket = $abfrage->fetch();
if (!$ticket) {
    http_response_code(404);
    exit('Zugehöriges Ticket nicht gefunden.');
}

$fehlermeldungen = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['vorbedingung', 'fehlersituation', 'trigger_eingabe', 'erwartetes_ergebnis'] as $feld) {
        $testfall[$feld] = trim($_POST[$feld] ?? '');
    }

    if ($testfall['trigger_eingabe'] === '') {
        $fehlermeldungen[] = 'Trigger / Eingabesequenz darf nicht leer sein.';
    }
    if ($testfall['erwartetes_ergebnis'] === '') {
        $fehlermeldungen[] = 'Erwartetes Ergebnis darf nicht leer sein.';
    }

    if (!$fehlermeldungen) {
        $spalten_werte = [
            $testfall['vorbedingung'] !== '' ? $testfall['vorbedingung'] : null,
            $testfall['fehlersituation'] !== '' ? $testfall['fehlersituation'] : null,
            $testfall['trigger_eingabe'],
            $testfall['erwartetes_ergebnis'],
        ];

        if ($testfall_id === null) {
            db()->prepare(
                'INSERT INTO testfaelle (vorbedingung, fehlersituation, trigger_eingabe, erwartetes_ergebnis, ticket_id)
                 VALUES (?, ?, ?, ?, ?)'
            )->execute([...$spalten_werte, $ticket['id']]);
        } else {
            db()->prepare(
                'UPDATE testfaelle
                 SET vorbedingung = ?, fehlersituation = ?, trigger_eingabe = ?, erwartetes_ergebnis = ?
                 WHERE id = ?'
            )->execute([...$spalten_werte, $testfall_id]);
        }
        redirect('ticket_detail.php?id=' . $ticket['id']);
    }
}

$titel = $testfall_id === null
    ? 'Neuer Testfall für ' . ticket_nr((int)$ticket['id'])
    : 'Testfall ' . tf_nr($testfall_id) . ' bearbeiten';
require __DIR__ . '/../../src/partials/header.php';
?>
<h1><?= ($titel) ?></h1>
<p class="hinweis">Referenz: <a href="ticket_detail.php?id=<?= $ticket['id'] ?>"><?= ticket_nr((int)$ticket['id']) ?> – <?= ($ticket['kurztitel']) ?></a></p>

<?php if ($fehlermeldungen): ?>
    <div class="fehlerbox">
        <ul>
            <?php foreach ($fehlermeldungen as $fehlermeldung): ?><li><?= ($fehlermeldung) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" class="formular">
    <fieldset>
        <legend>Testfall</legend>
        <label>Vorbedingung <span class="hinweis">(optional)</span>
            <textarea name="vorbedingung" rows="3" placeholder="z. B. Mindestens ein Ticket ist angelegt"><?= ($testfall['vorbedingung'] ?? '') ?></textarea>
        </label>
        <label>Fehlersituation <span class="hinweis">(optional – nur bei Fehlerfällen)</span>
            <textarea name="fehlersituation" rows="3" placeholder="z. B. Pflichtfeld Objekt ist leer"><?= ($testfall['fehlersituation'] ?? '') ?></textarea>
        </label>
        <label>Trigger / Eingabesequenz *
            <textarea name="trigger_eingabe" rows="4" required placeholder="z. B. 1. Formular öffnen  2. Felder ausfüllen  3. Speichern klicken"><?= ($testfall['trigger_eingabe']) ?></textarea>
        </label>
        <label>Erwartetes Ergebnis *
            <textarea name="erwartetes_ergebnis" rows="3" required placeholder="z. B. Das Ticket erscheint in der Spalte Ready"><?= ($testfall['erwartetes_ergebnis']) ?></textarea>
        </label>
    </fieldset>
    <div class="aktionen">
        <button type="submit" class="btn btn-primary">Speichern</button>
        <a class="btn" href="ticket_detail.php?id=<?= $ticket['id'] ?>">Abbrechen</a>
    </div>
</form>
<?php require __DIR__ . '/../../src/partials/footer.php'; ?>
