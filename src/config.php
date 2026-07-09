<?php

// Zentrale DB-Konfiguration – einzige Stelle mit Zugangsdaten.
// Reihenfolge: config.local.php (Server, nicht in Git)
//            > Umgebungsvariablen (Docker)
//            > lokale Entwicklungs-Defaults (php -S + Homebrew-MySQL).
$konfig_lokal = is_file(__DIR__ . '/config.local.php')
    ? require __DIR__ . '/config.local.php'
    : [];

define('DB_DSN', sprintf(
    'mysql:host=%s;dbname=%s;charset=utf8mb4',
    $konfig_lokal['host'] ?? (getenv('DB_HOST') ?: '127.0.0.1'),
    $konfig_lokal['name'] ?? (getenv('DB_NAME') ?: 'typeshyt')
));
define('DB_USER', $konfig_lokal['user'] ?? (getenv('DB_USER') ?: 'root'));
define('DB_PASS', $konfig_lokal['pass'] ?? (getenv('DB_PASS') ?: ''));

// URL-Präfix, wenn das Projekt in einem Unterordner der Domain läuft
// (Server: '/typeshyt'). Lokal und im Docker-Container leer.
define('BASIS_URL', $konfig_lokal['basis_url'] ?? (getenv('BASIS_URL') ?: ''));
