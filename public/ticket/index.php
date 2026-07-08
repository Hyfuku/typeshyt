<?php

require_once __DIR__ . '/../../src/helpers.php';

$alle_tickets = db()->query(
    "SELECT t.*,
            (SELECT COUNT(*) FROM testfaelle tf WHERE tf.ticket_id = t.id) AS tf_anzahl
     FROM tickets t
     ORDER BY FIELD(t.prioritaet, 'hoch', 'mittel', 'niedrig'), t.id"
)->fetchAll();

$spalten = array_fill_keys(array_keys(STATUS_SPALTEN), []);
foreach ($alle_tickets as $ticket) {
    $spalten[$ticket['status']][] = $ticket;
}

$titel = 'Board';
require __DIR__ . '/../../src/partials/header.php';
?>
<div class="board">
    <?php foreach (STATUS_SPALTEN as $status => $label): ?>
        <section class="spalte" data-status="<?= $status ?>">
            <h2><?= $label ?> <span class="anzahl"><?= count($spalten[$status]) ?></span></h2>
            <div class="karten">
                <?php foreach ($spalten[$status] as $ticket): ?>
                    <article class="karte prio-<?= $ticket['prioritaet'] ?>" draggable="true" data-id="<?= $ticket['id'] ?>">
                        <a class="karte-link" href="ticket_detail.php?id=<?= $ticket['id'] ?>">
                            <span class="ticket-nr"><?= ticket_nr((int)$ticket['id']) ?></span>
                            <span class="kurztitel"><?= ($ticket['kurztitel']) ?></span>
                        </a>
                        <footer>
                            <span class="badge badge-prio"><?= PRIORITAETEN[$ticket['prioritaet']] ?></span>
                            <?php if ($ticket['aufwand_pt'] !== null): ?>
                                <span class="badge"><?= (pt_format($ticket['aufwand_pt'])) ?></span>
                            <?php endif; ?>
                            <?php if ($ticket['tf_anzahl'] > 0): ?>
                                <span class="badge">⧉ <?= $ticket['tf_anzahl'] ?> Testfall<?= $ticket['tf_anzahl'] > 1 ? 'e' : '' ?></span>
                            <?php endif; ?>
                        </footer>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>
<script src="js/board.js"></script>
<?php require __DIR__ . '/../../src/partials/footer.php'; ?>
