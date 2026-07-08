<?php

require_once __DIR__ . '/../../src/helpers.php';

$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$abfrage = db()->prepare('SELECT * FROM tickets WHERE id = ?');
$abfrage->execute([$ticket_id]);
$ticket = $abfrage->fetch();
if (!$ticket) {
    http_response_code(404);
    exit('Ticket nicht gefunden.');
}

$abfrage = db()->prepare('SELECT * FROM testfaelle WHERE ticket_id = ? ORDER BY id');
$abfrage->execute([$ticket_id]);
$testfaelle = $abfrage->fetchAll();

$titel = ticket_nr($ticket_id) . ' – ' . $ticket['kurztitel'];
require __DIR__ . '/../../src/partials/header.php';
?>
<div class="detail-kopf">
    <h1><span class="ticket-nr"><?= ticket_nr($ticket_id) ?></span> <?= ($ticket['kurztitel']) ?></h1>
    <div class="aktionen">
        <a class="btn" href="ticket_form.php?id=<?= $ticket_id ?>">Bearbeiten</a>
        <form method="post" action="ticket_delete.php" onsubmit="return confirm('Ticket <?= ticket_nr($ticket_id) ?> und alle zugehörigen Testfälle löschen?')">
            <input type="hidden" name="id" value="<?= $ticket_id ?>">
            <button type="submit" class="btn btn-gefahr">Löschen</button>
        </form>
    </div>
</div>

<blockquote class="rupp-satz"><?= (rupp_satz($ticket)) ?></blockquote>

<dl class="meta">
    <dt>Status</dt><dd><?= STATUS_SPALTEN[$ticket['status']] ?></dd>
    <dt>Priorität</dt><dd><span class="badge badge-prio prio-<?= $ticket['prioritaet'] ?>"><?= PRIORITAETEN[$ticket['prioritaet']] ?></span></dd>
    <dt>Quelle / Stakeholder</dt><dd><?= $ticket['quelle_stakeholder'] !== null ? ($ticket['quelle_stakeholder']) : '–' ?></dd>
    <dt>Geplanter Personalaufwand</dt><dd><?= pt_format($ticket['aufwand_pt']) ?? '–' ?></dd>
    <dt>Angelegt</dt><dd><?= date('d.m.Y H:i', strtotime($ticket['created_at'])) ?></dd>
    <dt>Zuletzt geändert</dt><dd><?= date('d.m.Y H:i', strtotime($ticket['updated_at'])) ?></dd>
</dl>

<section class="testfaelle">
    <div class="abschnitt-kopf">
        <h2>Testfälle (<?= count($testfaelle) ?>)</h2>
        <a class="btn btn-primary" href="testfall_form.php?ticket_id=<?= $ticket_id ?>">+ Neuer Testfall</a>
    </div>

    <?php if (!$testfaelle): ?>
        <p class="leer">Noch keine Testfälle für dieses Ticket.</p>
    <?php endif; ?>

    <?php foreach ($testfaelle as $testfall): ?>
        <article class="testfall">
            <header>
                <strong><?= tf_nr((int)$testfall['id']) ?></strong>
                <span class="hinweis">Referenz: <?= ticket_nr($ticket_id) ?></span>
                <span class="tf-aktionen">
                    <a class="btn btn-klein" href="testfall_form.php?id=<?= $testfall['id'] ?>">Bearbeiten</a>
                    <form method="post" action="testfall_delete.php" onsubmit="return confirm('Testfall <?= tf_nr((int)$testfall['id']) ?> löschen?')">
                        <input type="hidden" name="id" value="<?= $testfall['id'] ?>">
                        <button type="submit" class="btn btn-klein btn-gefahr">Löschen</button>
                    </form>
                </span>
            </header>
            <dl>
                <dt>Vorbedingung</dt><dd><?= $testfall['vorbedingung'] !== null && $testfall['vorbedingung'] !== '' ? nl2br(($testfall['vorbedingung'])) : '–' ?></dd>
                <dt>Fehlersituation</dt><dd><?= $testfall['fehlersituation'] !== null && $testfall['fehlersituation'] !== '' ? nl2br(($testfall['fehlersituation'])) : '–' ?></dd>
                <dt>Trigger / Eingabesequenz</dt><dd><?= nl2br(($testfall['trigger_eingabe'])) ?></dd>
                <dt>Erwartetes Ergebnis</dt><dd><?= nl2br(($testfall['erwartetes_ergebnis'])) ?></dd>
            </dl>
        </article>
    <?php endforeach; ?>
</section>

<p><a href="index.php">← Zurück zum Board</a></p>
<?php require __DIR__ . '/../../src/partials/footer.php'; ?>
