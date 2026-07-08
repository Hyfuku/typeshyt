// Drag & Drop der Ticket-Karten zwischen den Spalten (natives HTML5 DnD).
document.querySelectorAll('.karte').forEach(karte => {
    karte.addEventListener('dragstart', e => {
        e.dataTransfer.setData('text/plain', karte.dataset.id);
        e.dataTransfer.effectAllowed = 'move';
        karte.classList.add('dragging');
    });
    karte.addEventListener('dragend', () => karte.classList.remove('dragging'));
});

document.querySelectorAll('.spalte').forEach(spalte => {
    spalte.addEventListener('dragover', e => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        spalte.classList.add('drop-ziel');
    });

    spalte.addEventListener('dragleave', e => {
        if (!spalte.contains(e.relatedTarget)) {
            spalte.classList.remove('drop-ziel');
        }
    });

    spalte.addEventListener('drop', async e => {
        e.preventDefault();
        spalte.classList.remove('drop-ziel');

        const id = e.dataTransfer.getData('text/plain');
        const karte = document.querySelector(`.karte[data-id="${id}"]`);
        if (!karte || karte.closest('.spalte') === spalte) return;

        const antwort = await fetch('api/status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: Number(id), status: spalte.dataset.status}),
        });

        if (antwort.ok) {
            spalte.querySelector('.karten').appendChild(karte);
            aktualisiereAnzahlen();
        } else {
            alert('Status konnte nicht gespeichert werden.');
        }
    });
});

function aktualisiereAnzahlen() {
    document.querySelectorAll('.spalte').forEach(spalte => {
        spalte.querySelector('.anzahl').textContent = spalte.querySelectorAll('.karte').length;
    });
}
