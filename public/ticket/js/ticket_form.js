// Live-Vorschau des Anforderungssatzes nach Rupp.
// Gleiche Logik wie rupp_satz() in src/helpers.php.
const form = document.getElementById('ticket-form');
const vorschau = document.getElementById('rupp-vorschau');
const akteurFeld = document.getElementById('akteur-feld');

function wert(name) {
    return form.elements[name].value.trim();
}

function ruppSatz() {
    const system = wert('system_name') || 'das System';
    const objekt = wert('objekt') || '…';
    const prozesswort = wert('prozesswort') || '…';
    const funktyp = wert('funktyp');
    const verbindlichkeit = wert('verbindlichkeit');
    const bedingung = wert('bedingung');

    let satzkern;
    if (funktyp === 'benutzerinteraktion') {
        satzkern = `${wert('akteur') || 'dem Nutzer'} die Möglichkeit bieten, ${objekt} zu ${prozesswort}`;
    } else if (funktyp === 'schnittstelle') {
        satzkern = `fähig sein, ${objekt} zu ${prozesswort}`;
    } else {
        satzkern = `${objekt} ${prozesswort}`;
    }

    if (bedingung !== '') {
        return `${bedingung.replace(/[ ,]+$/, '')}, ${verbindlichkeit} ${system} ${satzkern}.`;
    }
    return `${system.charAt(0).toUpperCase()}${system.slice(1)} ${verbindlichkeit} ${satzkern}.`;
}

function aktualisiere() {
    vorschau.textContent = ruppSatz();
    akteurFeld.style.display = wert('funktyp') === 'benutzerinteraktion' ? '' : 'none';
}

form.addEventListener('input', aktualisiere);
aktualisiere();
