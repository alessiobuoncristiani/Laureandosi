# 🎓 Laureandosi - Sistema di Gestione Prospetti di Laurea

Un sistema completo per l'automazione, il calcolo e la generazione dei prospetti di laurea per le commissioni e i candidati. 

Progetto realizzato per l'esame di **Ingegneria del Software** (Prof. Mario G. Cimino) presso il Dipartimento di Ingegneria dell'Informazione dell'**Università di Pisa** (A.A. 2025-2026).

---

## 📖 Panoramica del Progetto
L'obiettivo del progetto è stato l'applicazione rigorosa del metodo **Unified Process (UP)** in tutte le fasi del ciclo di vita del software: dall'ingegnerizzazione dei requisiti (tramite MoSCoW method), passando per l'analisi (Visual Paradigm) e il design (Mermaid), fino all'implementazione e al collaudo (Unit Testing).

Il sistema finale, sviluppato in PHP come tema/modulo per WordPress, si interfaccia con le API di Ateneo per estrarre anagrafiche e carriere (in JSON), calcola le medie ponderate applicando regole specifiche per ogni corso e genera dinamicamente i PDF ufficiali per le sedute di laurea, inviandoli automaticamente agli studenti via email.

## 🛠️ Tecnologie e Strumenti
*   **Backend & Logica:** PHP (Sviluppato su IDE PhpStorm)
*   **Ambiente di Produzione:** WordPress (Local by Flywheel)
*   **Modellazione Analisi:** Visual Paradigm (Casi d'uso, CRC, Classi, Sequenza)
*   **Modellazione Progetto:** Mermaid (Diagrammi di classe e sequenza di progetto)
*   **Librerie Esterne:** FPDF (Generazione PDF), PHPMailer (Gestione invio email)
*   **Formato Dati:** JSON (Configurazioni, API Ateneo, Unit Test)

---

## 📂 Struttura del Repository

```text
📦 Laureandosi
 ┣ 📂 classi/                # Core logic: DTO, Calcolatori voto e Generatori PDF
 ┣ 📂 config/                # File JSON: cdl, filtri, esami_informatici, TestOutput
 ┣ 📂 dati_laureandi/        # Dati JSON mockati delle carriere (per testing locale)
 ┣ 📂 Documenti Laureandosi/ # Documentazione completa (Requisiti, Use Cases, Manuali)
 ┣ 📂 js/                    # Logica frontend (script.js per UI asincrona)
 ┣ 📂 lib/                   # Librerie esterne (FPDF, PHPMailer)
 ┣ 📂 Prospetti_Correnti/    # Cartella di output temporanea (Privacy by design)
 ┣ 📂 test/                  # Ambiente visivo di Unit Test (prevenzione regressioni)
 ┣ 📜 api.php                # Endpoint per la comunicazione col frontend
 ┣ 📜 index.php              # Entry point e Interfaccia Grafica (UI)
 ┣ 📜 style.css              # Fogli di stile
 ┗ 📜 Laureandosi.vpp        # File sorgente Visual Paradigm
```

---

## 🎯 Funzionalità Principali

*   **Integrazione Dati (API):** Prelievo asincrono delle carriere (esami, CFU, date) dal sistema esterno "Gestione Carriera Studente".
*   **Motore di Calcolo Dinamico:** Calcolo della media pesata e del voto base. Gestisce eccezioni avanzate come:
    *   *Filtri custom:* Esclusione di esami sovrannumerari o extracurriculari.
    *   *Bonus Temporale:* Scarto dell'esame peggiore per studenti in corso.
    *   *Media di Settore:* Isolamento degli esami informatici (ING-INF/05) per Ingegneria Informatica.
*   **Generazione PDF:** 
    *   *Prospetto Commissione:* Documento aggregato con tabelle previsionali del voto di laurea (Parametri Tesi e Commissione variabili per CdL).
    *   *Prospetto Laureando:* Documento individuale generato e allegato automaticamente all'email istituzionale.
*   **Privacy by Design (GDPR):** Architettura *stateless*. Le carriere vengono elaborate in RAM e i PDF generati vengono distrutti automaticamente a fine sessione.

---

## ⚙️ Configurazione Zero-Code

L'applicativo è progettato per essere scalabile dall'Amministratore senza alterare il codice PHP. Le logiche di business risiedono in `config/`:
*   `cdl.json`: Definisce i Corsi di Laurea, i CFU necessari e le formule matematiche parametriche (es. `M*3+18+T+C`).
*   `filtri.json`: Regole di esclusione esami (sia globali con wildcard `*`, sia per singola matricola).
*   `esami_informatici.json`: Dizionario aggiornabile degli esami validi per la "Media Informatica".

---

## 🧪 Ambiente di Collaudo (Unit Test)

Il sistema include un motore di Unit Test integrato per prevenire regressioni matematiche a seguito di modifiche ai file di configurazione.
*   Navigando sull'endpoint `/?test`, il sistema carica `TestExpectedOutput.json` (contenente casi di test certificati).
*   Vengono confrontati a runtime i valori Calcolati (CAL) con quelli Attesi (ATT).
*   Restituisce una dashboard visiva immediata: **OK** (verde) o **FAIL** (rosso).

---

## 🚀 Installazione e Avvio Rapido

Il software è pacchettizzato come tema per WordPress. Per testarlo in locale:

1. Installa [Local by Flywheel](https://localwp.com/) e crea un nuovo sito WordPress vuoto.
2. Dal pannello di Local, clicca su *Site folder* e naviga in: `app/public/wp-content/themes/`.
3. Clona o incolla questo repository rinominando la cartella in `Laureandosi`.
4. Avvia il sito e accedi alla dashboard di amministrazione (`WP Admin`).
5. Vai in **Aspetto > Temi** e clicca su **Attiva** sul riquadro *Laureandosi*.
6. Vai alla homepage del sito per utilizzare l'interfaccia dell'Unità Didattica.

> ⚠️ **Nota per il testing:** Assicurati di inserire i file JSON mockati di anagrafica e carriera in `dati_laureandi/` come specificato nel *Manuale di Installazione* (pag. 19 della documentazione).

---
*Progetto sviluppato da [Alessio Buoncristiani](https://github.com/alessio-buoncristiani)*
