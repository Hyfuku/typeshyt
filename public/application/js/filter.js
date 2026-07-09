// Visualisiert die UND-Verkettung der Filterleiste:
// - aktive Filter (Feld nicht leer) bekommen den blauen Doppelring (.aktiv)
// - Verbinder innerhalb der Kette aktiver Filter werden zur Linie (.linie)
// - Verbinder direkt vor einem aktiven Filter tragen den UND-Chip (.und)
// Das Styling der Klassen liegt in scss/filter.scss.
function aktualisiereVerbinder() {
    const leiste = document.querySelector('.filter-leiste');
    const pills = [...leiste.querySelectorAll('.filter-pill')];
    const verbinder = [...leiste.querySelectorAll('.filter-verbinder')];

    const aktiv = pills.map(pill =>
        pill.querySelector('input, select').value.trim() !== ''
    );

    pills.forEach((pill, i) => pill.classList.toggle('aktiv', aktiv[i]));

    const aktiveIndizes = aktiv.flatMap((istAktiv, i) => istAktiv ? [i] : []);
    const ketteVon = aktiveIndizes.length ? aktiveIndizes[0] : null;
    const ketteBis = aktiveIndizes.length ? aktiveIndizes[aktiveIndizes.length - 1] : null;

    verbinder.forEach((element, lueckenindex) => {
        const inKette = ketteVon !== null && ketteVon <= lueckenindex && lueckenindex < ketteBis;
        element.classList.toggle('linie', inKette);
        element.classList.toggle('und', inKette && aktiv[lueckenindex + 1]);
    });

    // „Alle Bewerbungen" ist aktiv, wenn ?alle=1 gesetzt ist und kein Filter greift
    const alleKnopf = document.getElementById('alle-knopf');
    if (alleKnopf) {
        const alleGewaehlt = new URLSearchParams(location.search).get('alle') === '1';
        alleKnopf.classList.toggle('aktiv', alleGewaehlt && aktiveIndizes.length === 0);
    }
}

document.querySelector('.filter-leiste').addEventListener('input', aktualisiereVerbinder);
aktualisiereVerbinder();
