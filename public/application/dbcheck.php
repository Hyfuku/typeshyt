<?php

require_once __DIR__ . '/../../src/helpers.php';

$form_bewerbungsnr = trim($_GET['bewerbungsnr'] ?? '');
$form_bewerbernr   = trim($_GET['bewerbernr'] ?? '');
$form_status       = $_GET['status'] ?? '';

$bedingungen = [];
$parameter   = [];

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

// Ohne aktiven Filter wird erst gar nicht abgefragt – die Tabelle erscheint
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
    $abfrage->execute($parameter);
    $bewerbungen = $abfrage->fetchAll();
}

// Welche Filter sind aktiv? Reihenfolge = Reihenfolge der Pills in der Leiste.
// Daraus entstehen die Verbindungslinien: jeder Verbinder zwischen zwei Pills
// wird zur Linie, wenn er innerhalb der Kette aktiver Filter liegt (= die
// WHERE-Klausel verbindet beide Seiten), und trägt das UND, wenn der Filter
// rechts von ihm aktiv ist.
$filter_aktiv = [
    $form_bewerbungsnr !== '',
    $form_bewerbernr !== '',
    $form_status !== '',
];
$aktive_indizes = array_keys(array_filter($filter_aktiv));
$kette_von = $aktive_indizes ? min($aktive_indizes) : null;
$kette_bis = $aktive_indizes ? max($aktive_indizes) : null;

function verbinder_klassen(int $lueckenindex, ?int $kette_von, ?int $kette_bis, array $filter_aktiv): string
{
    $klassen = 'filter-verbinder';
    if ($kette_von !== null && $kette_von <= $lueckenindex && $lueckenindex < $kette_bis) {
        $klassen .= ' linie';
        if ($filter_aktiv[$lueckenindex + 1]) {
            $klassen .= ' und';
        }
    }
    return $klassen;
}

$titel = 'Datenbank';

?>
<link rel="stylesheet" href="/css/style.css">
<link rel="stylesheet" href="/css/filter.css">

<main>
<h1>Datenbank</h1>

<form method="get" class="filter-leiste">
    <div class="filter-pill<?= $filter_aktiv[0] ? ' aktiv' : '' ?>">
        <input type="text" name="bewerbungsnr" placeholder="Bewerbungsnr"
               aria-label="Bewerbungsnr" value="<?= $form_bewerbungsnr ?>">
    </div>
    <div class="<?= verbinder_klassen(0, $kette_von, $kette_bis, $filter_aktiv) ?>"></div>

    <div class="filter-pill<?= $filter_aktiv[1] ? ' aktiv' : '' ?>">
        <input type="text" name="bewerbernr" placeholder="Bewerbernr"
               aria-label="Bewerbernr" value="<?= $form_bewerbernr ?>">
    </div>
    <div class="<?= verbinder_klassen(1, $kette_von, $kette_bis, $filter_aktiv) ?>"></div>

    <div class="filter-pill<?= $filter_aktiv[2] ? ' aktiv' : '' ?>">
        <select name="status" aria-label="Bearbeitungsstatus" onchange="this.form.submit()">
            <option value="">Bearbeitungsstatus</option>
            <?php foreach (BEARBEITUNGSSTATUS as $status): ?>
                <option value="<?= $status ?>" <?= $form_status === $status ? 'selected' : '' ?>><?= $status ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filter-aktionen">
        <button type="submit" class="pill-knopf">Filtern</button>
        <a class="pill-knopf<?= $alle_anzeigen && !$bedingungen ? ' aktiv' : '' ?>" href="dbcheck.php?alle=1">Alle Bewerbungen</a>
        <?php if ($aktive_indizes || $alle_anzeigen): ?>
            <a class="pill-knopf zuruecksetzen" href="dbcheck.php">Zurücksetzen <span class="kreuz">×</span></a>
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
