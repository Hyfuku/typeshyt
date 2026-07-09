<?php

require_once __DIR__ . '/../../src/helpers.php';  #enthält require_once __DIR__ . '/db.php' --> DB Connection enthält

$sql = db()->prepare('SELECT * FROM bewerbungen'); #sql statement zur Ausgabe aller Bewerbungen
$sql->execute(); #Datenbankabfrage
$bewerbungen = $sql->fetchAll(); #Ergebnis in Array Ausgabe

?>

<link rel="stylesheet" href="<?= url('/css/style.css') ?>"> #css styling

<main>
<h1>Bewerbungen</h1>
<table class="tabelle">
            <thead>
            <tr>
                <th>Bewerbungsnr</th>
                <th>Bewerbernr</th>
                <th>Arbeitszeit</th>
                <th>Bearbeitungsstatus</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$bewerbungen): ?>
                <tr>
                    <td colspan="4">Keine Bewerbungen gefunden.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($bewerbungen as $bewerbung): ?> <!---Ausgabe aller Daten in HTML Tabelle-->
                <tr>
                    <td><?= ($bewerbung['bewerbungsnr']) ?></td>
                    <td><?= ($bewerbung['bewerbernr']) ?></td>
                    <td><?= ($bewerbung['arbeitszeit']) ?></td>
                    <td><?= ($bewerbung['bearbeitungsstatus']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
</main>