document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('btnStartTest').addEventListener('click', eseguiTest);
});

function iconCheck() {
    return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="10" height="10"><polyline points="20 6 9 17 4 12"/></svg>`;
}
function iconX() {
    return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="10" height="10"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`;
}

function cmpCell(atteso, calcolato, uguale) {
    const cls = uguale ? 'cmp-match' : 'cmp-mismatch';
    return `
        <div class="cmp-cell ${cls}">
            <div class="cmp-expected">${atteso}</div>
            <div class="cmp-calc">${calcolato}</div>
        </div>`;
}

async function eseguiTest() {
    const btn            = document.getElementById('btnStartTest');
    const tbody          = document.getElementById('test-body');
    const statusBox      = document.getElementById('statusBox');
    const badgeStrong    = document.querySelector('#session-badge-test strong');
    const summaryContainer = document.getElementById('summary-container');

    btn.disabled = true;
    badgeStrong.textContent = 'in esecuzione…';
    tbody.innerHTML = `<tr><td colspan="7" class="empty-state">Esecuzione test in corso…</td></tr>`;
    summaryContainer.classList.add('hidden');
    statusBox.className = 'status-msg msg-loading';
    statusBox.textContent = 'Comunicazione con le API di test…';
    statusBox.classList.remove('hidden');

    try {
        const response = await fetch(apiTestEndpoint, { method: 'GET' });
        const data = await response.json();
        if (!data.success) throw new Error(data.message);

        tbody.innerHTML = '';
        let passati = 0;

        data.risultati.forEach(res => {
            if (res.esito) passati++;

            const tr = document.createElement('tr');
            tr.className = 'fade-in';

            const esitoHtml = res.esito
                ? `<span class="pill pill-pass">${iconCheck()} OK</span>`
                : `<span class="pill pill-fail">${iconX()} FAIL</span>`;

            if (res.errore !== '') {
                tr.innerHTML = `
                    <td><span class="badge-mat">${res.matricola}</span></td>
                    <td>${res.nomeAtteso}</td>
                    <td colspan="4"><span class="skip-note">${res.errore}</span></td>
                    <td>${esitoHtml}</td>
                `;
            } else {
                const d     = res.dati;
                const isInf = res.cdl === 'T. Ing. Informatica';

                const nomeMatch = res.nomeReale === res.nomeAtteso;
                const mediaMatch = Math.abs(parseFloat(d.mediaCalc) - parseFloat(d.mediaAttesa)) < 0.001;
                const cfuMatch  = (d.cfuMediaCalc === d.cfuMediaAttesi) && (d.cfuTotCalc === d.cfuTotAttesi);
                const bonusMatch = !isInf || (d.bonusCalc === d.bonusAtteso);
                const infMatch   = !isInf || (Math.abs(parseFloat(d.mediaInfCalc) - parseFloat(d.mediaInfAttesa)) < 0.001);

                const bonusAttesoStr = isInf ? (d.bonusAtteso ? 'SI' : 'NO') : '—';
                const bonusCalcStr   = isInf ? (d.bonusCalc   ? 'SI' : 'NO') : '—';
                const infAttesoStr   = isInf ? d.mediaInfAttesa : '—';
                const infCalcStr     = isInf ? d.mediaInfCalc   : '—';

                tr.innerHTML = `
                    <td><span class="badge-mat">${res.matricola}</span></td>
                    <td>${cmpCell(res.nomeAtteso, res.nomeReale, nomeMatch)}</td>
                    <td>${cmpCell(d.mediaAttesa, d.mediaCalc, mediaMatch)}</td>
                    <td>${cmpCell(d.cfuMediaAttesi + ' / ' + d.cfuTotAttesi, d.cfuMediaCalc + ' / ' + d.cfuTotCalc, cfuMatch)}</td>
                    <td>${cmpCell(bonusAttesoStr, bonusCalcStr, bonusMatch)}</td>
                    <td>${cmpCell(infAttesoStr, infCalcStr, infMatch)}</td>
                    <td>${esitoHtml}</td>
                `;
            }

            tbody.appendChild(tr);
        });

        const totale  = data.risultati.length;
        const falliti = totale - passati;
        document.getElementById('s-total').textContent = totale;
        document.getElementById('s-pass').textContent  = passati;
        document.getElementById('s-fail').textContent  = falliti;
        summaryContainer.classList.remove('hidden');

        const allPass = passati === totale;
        statusBox.className  = allPass ? 'status-msg msg-success' : 'status-msg msg-error';
        statusBox.textContent = allPass
            ? `Tutti i ${totale} test superati con successo.`
            : `${falliti} test falliti su ${totale}. Le celle rosse indicano le discrepanze.`;

        badgeStrong.textContent = `${passati}/${totale} ${allPass ? 'OK' : '— errori'}`;

    } catch (error) {
        tbody.innerHTML = `<tr><td colspan="7" class="empty-state" style="color:var(--red-700);">${error.message}</td></tr>`;
        statusBox.className  = 'status-msg msg-error';
        statusBox.textContent = 'Errore API: ' + error.message;
        badgeStrong.textContent = 'errore';
    } finally {
        btn.disabled = false;
    }
}