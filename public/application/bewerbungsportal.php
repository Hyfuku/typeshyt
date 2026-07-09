<?php

require_once __DIR__ . '/../../src/helpers.php';

$form_bewerbungsnr = trim($_GET['bewerbungsnr'] ?? ''); //GET AUS URL
$form_bewerbernr   = trim($_GET['bewerbernr'] ?? '');
$form_status       = $_GET['status'] ?? '';

$bedingungen = [];
$parameter   = []; //VALUE DES SUBMIT FORMS

if ($form_bewerbungsnr !== '') {
    $bedingungen[] = 'bewerbungsnr = ?';
    $parameter[]   = $form_bewerbungsnr;
}
if ($form_bewerbernr !== '') {
    $bedingungen[] = 'bewerbernr LIKE ?';
    $parameter[]   = '%' . $form_bewerbernr . '%';
}
if (in_array($form_status, BEARBEITUNGSSTATUS, true)) {
    $bedingungen[] = 'bearbeitungsstatus = ?';
    $parameter[]   = $form_status;
} else {
    $form_status = '';
}

// Ohne aktiven Filter wird erst gar nicht abgefragt  die Tabelle erscheint
// nur bei gesetztem Filter oder explizit über den Button „Alle Bewerbungen".
$alle_anzeigen = ($_GET['alle'] ?? '') === '1';
$suche_aktiv   = $bedingungen || $alle_anzeigen;

$bewerbungen = [];
if ($suche_aktiv) {
    $sql = 'SELECT * FROM bewerbungen';
    if ($bedingungen) {
        $sql .= ' WHERE ' . implode(' AND ', $bedingungen);
    }
    $sql .= ' ORDER BY bewerbungsnr DESC';

    $abfrage = db()->prepare($sql);
    $abfrage->execute($parameter); //WERTE DER SUBMIT FORMS WERDEN EINGEFÜGT
    $bewerbungen = $abfrage->fetchAll();
}

?>
<link rel="stylesheet" href="<?= url('/css/style.css') ?>"> <!---Style CSS-->
<link rel="stylesheet" href="<?= url('/css/filter.css') ?>"> <!---Sytle CSS-->

<main>
    <h1>Datenbank</h1>

    <form method="get" class="filter-leiste">
        <div class="filter-pill">
            <input type="text" name="bewerbungsnr" placeholder="Bewerbungsnr"
                   aria-label="Bewerbungsnr" value="<?= $form_bewerbungsnr ?>">
        </div>
        <div class="filter-verbinder"></div>

        <div class="filter-pill">
            <input type="text" name="bewerbernr" placeholder="Bewerbernr"
                   aria-label="Bewerbernr" value="<?= $form_bewerbernr ?>">
        </div>
        <div class="filter-verbinder"></div>

        <div class="filter-pill">
            <select name="status" aria-label="Bearbeitungsstatus" onchange="this.form.submit()">
                <option value="">Bearbeitungsstatus</option>
                <?php foreach (BEARBEITUNGSSTATUS as $status): ?>
                    <option value="<?= $status ?>" <?= $form_status === $status ? 'selected' : '' ?>><?= $status ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-aktionen">
            <button type="submit" class="pill-knopf">Filtern</button>
            <a class="pill-knopf<?= $alle_anzeigen && !$bedingungen ? ' aktiv' : '' ?>" href="bewerbungsportal.php?alle=1">Alle Bewerbungen</a>
            <?php if ($bedingungen || $alle_anzeigen): ?>
                <a class="pill-knopf zuruecksetzen" href="bewerbungsportal.php">Zurücksetzen <span class="kreuz">×</span></a>
            <?php endif; ?>
        </div>
    </form>

    <?php if (!$suche_aktiv): ?>
        <p class="leer">Filter setzen oder „Alle Bewerbungen" anzeigen.</p>
    <?php else: ?>
        <p><?= count($bewerbungen) ?> Bewerbung<?= count($bewerbungen) === 1 ? '' : 'en' ?> gefunden</p>

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
            <?php foreach ($bewerbungen as $bewerbung): ?>
                <tr>
                    <td><?= ($bewerbung['bewerbungsnr']) ?></td>
                    <td><?= ($bewerbung['bewerbernr']) ?></td>
                    <td><?= ($bewerbung['arbeitszeit']) ?></td>
                    <td><?= ($bewerbung['bearbeitungsstatus']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
