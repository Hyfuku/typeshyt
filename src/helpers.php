<?php

require_once __DIR__ . '/db.php';

const PRIORITAETEN = [
    'hoch'    => 'Hoch',
    'mittel'  => 'Mittel',
    'niedrig' => 'Niedrig',
];

const VERBINDLICHKEITEN = [
    'muss' => 'muss (rechtlich verpflichtend)',
    'soll' => 'soll (dringend empfohlen)',
    'wird' => 'wird (zukünftig)',
];

const FUNKTYPEN = [
    'selbstaendig'        => 'Selbständige Systemaktivität',
    'benutzerinteraktion' => 'Benutzerinteraktion',
    'schnittstelle'       => 'Schnittstellenanforderung',
];

const STATUS_SPALTEN = [
    'ready'  => 'Ready',
    'doing'  => 'Doing',
    'finish' => 'Finish',
];

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function ticket_nr(int $id): string
{
    return sprintf('T-%03d', $id);
}

function tf_nr(int $id): string
{
    return sprintf('TF-%03d', $id);
}

function pt_format(?string $personentage): ?string
{
    if ($personentage === null || $personentage === '') {
        return null;
    }
    return str_replace('.', ',', rtrim(rtrim($personentage, '0'), '.')) . ' PT';
}

/**
 * Generiert den Anforderungssatz nach der Schablone von Rupp.
 * Gleiche Logik wie in public/js/ticket_form.js (Live-Vorschau).
 */
function rupp_satz(array $ticket): string
{
    $system = trim($ticket['system_name'] ?? '') ?: 'das System';
    $objekt = trim($ticket['objekt'] ?? '');
    $prozesswort   = trim($ticket['prozesswort'] ?? '');

    $satzkern = match ($ticket['funktyp'] ?? 'selbstaendig') {
        'benutzerinteraktion' => (trim($ticket['akteur'] ?? '') ?: 'dem Nutzer')
            . " die Möglichkeit bieten, {$objekt} zu {$prozesswort}",
        'schnittstelle'       => "fähig sein, {$objekt} zu {$prozesswort}",
        default               => "{$objekt} {$prozesswort}",
    };

    $verbindlichkeit = $ticket['verbindlichkeit'] ?? 'muss';
    $bedingung = trim($ticket['bedingung'] ?? '');

    if ($bedingung !== '') {
        return rtrim($bedingung, ' ,') . ", {$verbindlichkeit} {$system} {$satzkern}.";
    }
    return mb_strtoupper(mb_substr($system, 0, 1)) . mb_substr($system, 1) . " {$verbindlichkeit} {$satzkern}.";
}

const BEARBEITUNGSSTATUS = ['offen', 'angenommen', 'in Bearbeitung', 'abgelehnt', 'vollständig'];
