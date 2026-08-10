# 🎓 Laureandosi - Sistema di Gestione Prospetti di Laurea

[![Stack](https://img.shields.io/badge/Stack-PHP%20%7C%20WordPress%20%7C%20JSON-blue)](#)
[![Course](https://img.shields.io/badge/Corso-Ingegneria%20del%20Software%20UniPi-red)](#)
[![UML](https://img.shields.io/badge/Design-Visual%20Paradigm%20%7C%20UML-orange)](#)

**Laureandosi** è un progetto completo di Ingegneria del Software sviluppato per l'Anno Accademico 2025-2026 presso l'Università di Pisa[cite: 1]. 
L'obiettivo assegnato consisteva nello sviluppare un'applicazione seguendo rigorosamente tutte le fasi dell'Ingegneria del Software, applicando il metodo **Unified Process (UP)** e producendo i relativi modelli dei requisiti, di analisi e di progetto tramite diagrammi UML.

Il software finale è un "Generatore Prospetti di Laurea" multipiattaforma[cite: 1]. L'applicativo si interfaccia con il sistema informativo di Ateneo ("Gestione Carriera Studente") per estrarre l'anagrafica e la carriera degli studenti in formato JSON, calcolare le medie e generare dinamicamente i documenti PDF ufficiali per le sedute di laurea[cite: 1].

## 🛠️ Architettura e Tecnologie Utilizzate

Il sistema è stato progettato ponendo forte enfasi sulla modularità e sul rispetto delle normative di ateneo sulla privacy (GDPR)[cite: 1]. Il software, infatti, conserva solo i dati personali strettamente necessari ed elimina automaticamente i prospetti generati ad ogni nuova sessione[cite: 1].

*   **Linguaggio Core:** PHP[cite: 1].
*   **Ambiente di Sviluppo:** Sviluppato utilizzando l'IDE PhpStorm[cite: 1].
*   **Ambiente di Produzione/Deploy:** Il sistema è pacchettizzato per essere eseguito su ambiente WordPress tramite server locale **Local (by Flywheel)**[cite: 1].
*   **Modellazione Software:** L'intera architettura (Casi d'Uso, CRC Cards, Diagrammi di Classe, di Sequenza e di Dislocazione) è stata modellata e prodotta utilizzando **Visual Paradigm**[cite: 1].
*   **Formato Interscambio Dati:** JSON[cite: 1].

## 📂 Struttura del Progetto e File di Configurazione

Il sistema è stato progettato per essere altamente manutenibile dall'Amministratore senza dover ricompilare il codice sorgente, grazie a una serie di file di configurazione presenti nella directory `/config/`[cite: 1]:

*   **`cdl.json`:** Contiene le informazioni relative ai corsi di laurea, inclusi i CFU totali e le formule specifiche (con i parametri 'T' per Tesi e 'C' per Commissione) per il calcolo del voto di partenza[cite: 1].
*   **`filtri.json`:** Gestisce le regole di filtraggio per escludere determinati esami dal calcolo della media[cite: 1]. Include regole applicabili a livello globale (simbolo jolly `*`) o per singola matricola[cite: 1].
*   **`esami_informatici.json`:** Contiene la lista degli esami appartenenti al settore INF, necessari per il calcolo della media speciale nei corsi di Ingegneria Informatica[cite: 1].

## 🧪 Ambiente di Collaudo (Unit Test)

Il progetto integra un ambiente di Unit Test per garantire l'assenza di regressioni (es. errori matematici nel calcolo dei CFU o delle simulazioni di voto) a seguito di modifiche ai file di configurazione[cite: 1].
*   I dati di collaudo e i risultati attesi certificati sono memorizzati all'interno del file **`TestExpectedOutput.json`**[cite: 1].
*   L'ambiente di test è accessibile aggiungendo il parametro `/?test` all'URL principale dell'applicativo[cite: 1]. L'interfaccia esegue i test e confronta i risultati attesi (ATT) con quelli calcolati a runtime (CAL), restituendo un esito visivo (OK o FAIL)[cite: 1].

## 📖 Documentazione Allegata

Tutto l'iter ingegneristico e procedurale è documentato all'interno del file ufficiale del progetto: **`Buoncristiani.pdf`**[cite: 1]. 
All'interno di questo documento è possibile consultare[cite: 1]:
*   **Analisi dei Requisiti:** Requisiti Funzionali (MoSCoW method) e Non Funzionali[cite: 1].
*   **Workflow Analisi e Progetto:** CRC Cards, Specifiche dei Casi d'Uso, Diagrammi di Classe e Diagrammi di Sequenza[cite: 1].
*   **Manuali Operativi:** Manuale Utente per l'Unità Didattica, Manuale di Installazione per l'impostazione di WordPress tramite *Local*, e Manuale di Configurazione per l'Amministratore[cite: 1].

## 🚀 Guida Rapida all'Installazione

1. Installare **Local by Flywheel** per predisporre un ambiente web locale[cite: 1].
2. Creare un nuovo sito WordPress vuoto[cite: 1].
3. Spostare i file del progetto all'interno del percorso `app/public/wp-content/themes/` della cartella di installazione generata da Local[cite: 1].
4. Accedere alla dashboard di amministrazione di WordPress ("WP Admin") e attivare il tema corrispondente al progetto "Laureandosi" dal menù *Aspetto > Temi*[cite: 1].
