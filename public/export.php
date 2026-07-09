<?php

require_once __DIR__ . '/../src/helpers.php';

// Kompletter Datenbank-Export als SQL-Download:
// Struktur (SHOW CREATE TABLE) + alle Zeilen als INSERTs.
$tabellen = ['tickets', 'testfaelle', 'bewerbungen'];

header('Content-Type: application/sql; charset=utf-8');
header('Content-Disposition: attachment; filename="typeshyt-export-' . date('Y-m-d_H-i') . '.sql"');

echo "-- typeshyt – Datenbank-Export vom " . date('d.m.Y H:i') . "\n";
echo "SET FOREIGN_KEY_CHECKS = 0;\n\n";

foreach ($tabellen as $tabelle) {
    $erzeugung = db()->query("SHOW CREATE TABLE `$tabelle`")->fetch();
    echo "DROP TABLE IF EXISTS `$tabelle`;\n";
    echo $erzeugung['Create Table'] . ";\n\n";

    $zeilen = db()->query("SELECT * FROM `$tabelle`")->fetchAll();
    foreach ($zeilen as $zeile) {
        $spalten = '`' . implode('`, `', array_keys($zeile)) . '`';
        $spalten_werte = implode(', ', array_map(
            fn ($wert) => $wert === null ? 'NULL' : db()->quote((string)$wert),
            array_values($zeile)
        ));
        echo "INSERT INTO `$tabelle` ($spalten) VALUES ($spalten_werte);\n";
    }
    echo "\n";
}

echo "SET FOREIGN_KEY_CHECKS = 1;\n";
