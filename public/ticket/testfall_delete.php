<?php

require_once __DIR__ . '/../../src/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$testfall_id = (int)($_POST['id'] ?? 0);

$abfrage = db()->prepare('SELECT ticket_id FROM testfaelle WHERE id = ?');
$abfrage->execute([$testfall_id]);
$testfall = $abfrage->fetch();

db()->prepare('DELETE FROM testfaelle WHERE id = ?')->execute([$testfall_id]);
redirect($testfall ? 'ticket_detail.php?id=' . $testfall['ticket_id'] : 'index.php');
