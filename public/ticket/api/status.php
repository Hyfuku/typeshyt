<?php

require_once __DIR__ . '/../../../src/helpers.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'fehler' => 'Nur POST erlaubt']);
    exit;
}

$anfrage_daten  = json_decode(file_get_contents('php://input'), true);
$ticket_id     = (int)($anfrage_daten['id'] ?? 0);
$status = $anfrage_daten['status'] ?? '';

if ($ticket_id < 1 || !isset(STATUS_SPALTEN[$status])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'fehler' => 'Ungültige Eingabe']);
    exit;
}

$abfrage = db()->prepare('SELECT 1 FROM tickets WHERE id = ?');
$abfrage->execute([$ticket_id]);
if (!$abfrage->fetch()) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'fehler' => 'Ticket nicht gefunden']);
    exit;
}

db()->prepare('UPDATE tickets SET status = ? WHERE id = ?')->execute([$status, $ticket_id]);
echo json_encode(['ok' => true]);
