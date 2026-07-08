<?php

require_once __DIR__ . '/../../src/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$ticket_id = (int)($_POST['id'] ?? 0);
// Testfälle werden per ON DELETE CASCADE mitgelöscht
db()->prepare('DELETE FROM tickets WHERE id = ?')->execute([$ticket_id]);
redirect('index.php');
