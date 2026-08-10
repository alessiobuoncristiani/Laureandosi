// HELPERS
function mostraMessaggio(testo, tipo) {
    const box = document.getElementById('statusBox');
    box.className = `status-msg msg-${tipo}`;
    box.textContent = testo;
}

function setBottoni(apri, invia, crea) {
    document.getElementById('btn-apri').disabled  = !apri;
    document.getElementById('btn-invia').disabled = !invia;
    document.querySelector('.btn-primary').disabled = !crea;
}

async function checkProspettiEsistenti(cdl) {
    try {
        const fd = new FormData();
        fd.append('azione', 'check');
        fd.append('cdl', cdl);

        const res = await fetch(themeUrl + '/api.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            return { apri: data.apri, invia: data.invia };
        }
        return { apri: false, invia: false };
    } catch {
        return { apri: false, invia: false };
    }
}

// CAMBIO CORSO
document.getElementById('cdl').addEventListener('change', async function () {
    const cdl = this.value;
    const badgeCdl = document.getElementById('badge-cdl');

    if (!cdl) {
        badgeCdl.textContent = 'nessun corso selezionato';
        setBottoni(false, false, true);
        return;
    }

    badgeCdl.textContent = this.options[this.selectedIndex].text;

    // Abilita sempre Genera; blocca Apri/Invia in attesa del check reale
    setBottoni(false, false, true);

    // CORREZIONE: Allineamento bottoni al cambio corso
    const stato = await checkProspettiEsistenti(cdl);
    setBottoni(stato.apri, stato.invia, true);
});

// AZIONI PRINCIPALI
async function eseguiAzione(azione) {
    const cdl       = document.getElementById('cdl').value;
    const data      = document.getElementById('data-laurea').value;
    const matricole = document.getElementById('matricole').value;

    if (!cdl) {
        mostraMessaggio("Seleziona prima un Corso di Laurea.", "error");
        return;
    }
    if (azione === 'crea' && (!data || !matricole.trim())) {
        mostraMessaggio("Compila la data dell'appello e almeno una matricola.", "error");
        return;
    }

    // Blocca tutto durante l'operazione per prevenire click doppi
    setBottoni(false, false, false);

    // INVIO EMAIL
    if (azione === 'invia') {
        mostraMessaggio("Recupero elenco prospetti da inviare…", "loading");

        try {
            const fdLista = new FormData();
            fdLista.append('azione', 'lista_invio');
            fdLista.append('cdl', cdl);
            const resLista = await fetch(themeUrl + '/api.php', { method: 'POST', body: fdLista });
            const dataLista = await resLista.json();
            const listaMatricole = JSON.parse(dataLista.link);

            if (listaMatricole.length === 0) {
                mostraMessaggio("Nessun prospetto individuale trovato da inviare.", "error");
                const stato = await checkProspettiEsistenti(cdl);
                setBottoni(stato.apri, stato.invia, true);
                return;
            }

            document.getElementById('progress-container').classList.remove('hidden');
            let inviati = 0;

            for (let i = 0; i < listaMatricole.length; i++) {
                mostraMessaggio(`Invio in corso: ${listaMatricole[i]}…`, "loading");

                const fdSingolo = new FormData();
                fdSingolo.append('azione', 'invia_singolo');
                fdSingolo.append('cdl', cdl);
                fdSingolo.append('matricola_singola', listaMatricole[i]);

                const resSingolo = await fetch(themeUrl + '/api.php', { method: 'POST', body: fdSingolo });

                if (!resSingolo.ok) throw new Error("Connessione al server SMTP fallita. Controlla la VPN.");

                const dataSingolo = await resSingolo.json();
                if (!dataSingolo.success) throw new Error(dataSingolo.message);

                inviati++;
                const pct = (inviati / listaMatricole.length) * 100;
                document.getElementById('progress-bar-fill').style.width = pct + '%';
                document.getElementById('progress-text').innerText = `Email inviata ${inviati} di ${listaMatricole.length}`;

                if (i < listaMatricole.length - 1) {
                    await new Promise(r => setTimeout(r, 13000)); // Delay antispam
                }
            }

            mostraMessaggio(`Tutti i prospetti inviati con successo.`, "success");
            setTimeout(() => {
                document.getElementById('progress-container').classList.add('hidden');
                document.getElementById('progress-bar-fill').style.width = '0%';
            }, 3000);

            const stato = await checkProspettiEsistenti(cdl);
            setBottoni(stato.apri, stato.invia, true);

        } catch (error) {
            mostraMessaggio("Errore: " + error.message, "error");
            document.getElementById('progress-container').classList.add('hidden');

            const stato = await checkProspettiEsistenti(cdl);
            setBottoni(stato.apri, stato.invia, true);
        }
        return;
    }

    // GENERA / APRI
    const formData = new FormData();
    formData.append('azione', action = azione);
    formData.append('cdl', cdl);
    formData.append('data', data);
    formData.append('matricole', matricole);

    mostraMessaggio("Elaborazione in corso…", "loading");

    try {
        const response = await fetch(themeUrl + '/api.php', { method: 'POST', body: formData });
        const result = await response.json();

        if (result.success) {
            if (azione === 'crea') {
                mostraMessaggio(result.message, "success");
                const stato = await checkProspettiEsistenti(cdl);
                setBottoni(stato.apri, stato.invia, true);

            } else if (azione === 'apri') {
                window.open(themeUrl + "/" + result.link, '_blank');
                mostraMessaggio("Documento aperto in una nuova scheda.", "success");

                const stato = await checkProspettiEsistenti(cdl);
                setBottoni(stato.apri, stato.invia, true);
            }
        } else {
            mostraMessaggio(result.message, "error");
            const stato = await checkProspettiEsistenti(cdl);
            setBottoni(stato.apri, stato.invia, true);
        }

    } catch (error) {
        mostraMessaggio("Errore: Impossibile elaborare la richiesta. Controlla il server.", "error");
        setBottoni(false, false, true);
    }
}