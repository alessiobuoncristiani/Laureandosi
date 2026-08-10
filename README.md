# 🎓 Laureandosi - Sistema di Gestione Prospetti di Laurea

[![Stack](https://img.shields.io/badge/Stack-PHP%20%7C%20WordPress%20%7C%20JSON-blue)](#)
[![IDE](https://img.shields.io/badge/IDE-PhpStorm-purple)](#)
[![Tools](https://img.shields.io/badge/Tools-Local_by_Flywheel-green)](#)
[![Design](https://img.shields.io/badge/Design-Visual_Paradigm%20%7C%20Mermaid-orange)](#)

**Laureandosi** è un progetto completo di Ingegneria del Software sviluppato per l'Anno Accademico 2025-2026 presso l'Università di Pisa, sotto la docenza del Prof. Mario G. Cimino. 

L'obiettivo del progetto non è stato unicamente lo sviluppo del software, ma l'applicazione rigorosa del metodo **Unified Process (UP)** in tutte le fasi del ciclo di vita dell'applicativo: dall'ingegnerizzazione dei requisiti, passando per l'analisi e il design (tramite diagrammi UML), fino all'implementazione e al collaudo.

Il prodotto finale è un "Generatore Prospetti di Laurea" sviluppato in PHP per ambiente WordPress. Il sistema si interfaccia con le API di Ateneo per estrarre l'anagrafica e la carriera degli studenti (in formato JSON), calcola le medie ponderate applicando regole specifiche per ogni corso di laurea e genera dinamicamente i documenti PDF ufficiali per le sedute di laurea, inviandoli in automatico agli studenti.

---

## 🛠️ Architettura e Metodologia

Il sistema è stato progettato garantendo modularità, rispetto della privacy (GDPR) e completa separazione tra logica di calcolo, dati e presentazione.

*   **Metodologia:** Unified Process (UP) con analisi dei requisiti funzionali tramite metodo MoSCoW (Must, Should, Could, Want).
*   **Modellazione UML:** L'intera architettura (Casi d'Uso, Analisi CRC, Diagrammi di Classe, Sequenza e Dislocazione) è stata documentata utilizzando **Visual Paradigm** e **Mermaid**.
*   **Linguaggio & IDE:** Sviluppo backend in **PHP** tramite l'IDE **PhpStorm**.
*   **Ambiente di Deploy:** Integrazione nativa in ambiente **WordPress**, testata e messa in produzione su server locale tramite **Local (by Flywheel)**.
*   **Formato Interscambio:** Lettura ed elaborazione asincrona di dati in formato **JSON**.

---

## 🎯 Funzionalità Principali

*   **Integrazione Dati:** Prelievo automatico delle carriere (esami, CFU, date) dal sistema esterno "Gestione Carriera Studente".
*   **Motore di Calcolo Dinamico:** Algoritmi per il calcolo della media pesata e del voto di base. Il sistema gestisce eccezioni specifiche, come l'isolamento degli esami informatici (ING-INF/05) per il CdL in Ingegneria Informatica o lo scarto dell'esame peggiore per il calcolo del bonus temporale.
*   **Generazione PDF:** Creazione di due tipologie di documenti:
    *   *Prospetto Commissione:* Documento riassuntivo contenente tutti i candidati e una tabella previsionale con le proiezioni del voto di laurea in base ai punti assegnabili (Parametri Tesi e Commissione).
    *   *Prospetto Laureando:* Documento individuale generato e inviato automaticamente all'email istituzionale dello studente.
*   **Privacy by Design:** Il sistema è "stateless" per quanto riguarda i dati sensibili: elabora le carriere in RAM, genera i documenti ed elimina automaticamente i file al termine di ogni sessione, conformemente al GDPR.

---

## ⚙️ File di Configurazione (Sistema Flessibile)

L'applicativo è progettato per essere scalabile e configurabile dall'Amministratore senza necessità di alterare il codice sorgente (Zero-Code Maintenance). Tutte le logiche di business risiedono nella directory `/config/`:

*   `cdl.json`: Definisce i Corsi di Laurea supportati, il numero di CFU totali necessari e le formule matematiche parametriche (es. `M*3+18+T+C`) per la generazione delle tabelle di simulazione.
*   `filtri.json`: Gestisce l'esclusione di specifici esami dal calcolo della media o dai crediti curriculari (es. esami extracurriculari). Supporta regole globali (wildcard `*`) o eccezioni per singola matricola.
*   `esami_informatici.json`: Un dizionario aggiornabile contenente le nomenclature esatte degli esami validi per il calcolo della "Media Informatica".

---

## 🧪 Ambiente di Collaudo (Unit Test)

Il progetto integra un ambiente di Unit Test visivo, progettato per prevenire regressioni matematiche quando l'Amministratore modifica i file di configurazione o le regole di calcolo.

*   Accedendo all'endpoint `/?test`, il sistema carica il file `TestExpectedOutput.json`, che contiene i risultati matematici certificati (ATT - Atteso) per specifiche matricole di prova.
*   Il motore di calcolo elabora le carriere a runtime (CAL - Calcolato) e restituisce una dashboard visiva con l'esito dei test (OK verde se i valori coincidono, FAIL rosso in caso di discrepanze).

---

## 📖 Documentazione Allegata

Tutto il processo di ingegneria del software è ampiamente documentato nel file **`Buoncristiani.pdf`** incluso nel repository. Il documento comprende:
1. Analisi dei Requisiti (Funzionali e Non Funzionali) e Glossario.
2. Modelli di Analisi (Diagrammi Use Case, Specifiche, CRC Cards).
3. Modelli di Progetto (Diagrammi delle Classi, Sequenza, Dislocazione).
4. Manualistica completa (Manuale Utente, Installazione, Configurazione e Test).

---

## 🚀 Installazione e Avvio Rapido

Il sistema è pacchettizzato come tema/modulo per WordPress. Per testarlo in ambiente locale:

1. Installa [Local by Flywheel](https://localwp.com/) e crea un nuovo sito WordPress vuoto.
2. Dal pannello di Local, apri la *Site folder* e naviga nel percorso: `app/public/wp-content/themes/`.
3. Incolla all'interno di questa cartella l'intero progetto "Laureandosi".
4. Avvia il sito da Local e accedi alla dashboard di amministrazione (`WP Admin`).
5. Spostati nella sezione **Aspetto > Temi** e clicca su **Attiva** nel riquadro corrispondente a *Laureandosi*.
6. Visita la homepage del sito per accedere all'interfaccia operativa dell'Unità Didattica.

*(Nota: per il corretto funzionamento, assicurarsi di inserire nella directory apposita i file JSON di anagrafica e carriera mockati, come descritto nel Manuale di Installazione).*
