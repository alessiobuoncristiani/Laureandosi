<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambiente di Test — Laureandosi 2.0</title>
    <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/style.css">
    <script>const apiTestEndpoint = "<?php echo get_stylesheet_directory_uri(); ?>/test/test_api.php";</script>
</head>
<body>

<main class="dashboard" style="max-width: 1200px;">

    <header class="dash-header">
        <div class="header-inner">
            <div class="logo-area">
                <div class="logo-icon">🔬</div>
                <div>
                    <h1>Ambiente di Test</h1>
                    <p>Verifica automatica delle regole di calcolo per il voto di laurea</p>
                </div>
            </div>
            <div class="session-badge" id="session-badge-test">
                <span class="session-dot"></span>
                <span>Motore:&nbsp;</span>
                <strong>in attesa</strong>
            </div>
        </div>
    </header>

    <section class="dash-content" style="grid-template-columns: 1fr; gap: 1rem;">

        <!-- DESCRIZIONE -->
        <div class="info-box">
            <strong>Come funziona questa pagina</strong>
            <p>
                Il sistema legge un file <code>TestExpectedOutput.json</code> contenente i valori attesi per ogni matricola di test,
                poi interroga il motore di calcolo PHP e confronta i risultati reali con quelli attesi.
                Per ogni riga vengono verificati: media pesata, CFU che fanno media, CFU curriculari totali,
                bonus in-corso e media esami INF (solo per Ingegneria Informatica).
            </p>
            <ul>
                <li><strong>ATT</strong> = valore atteso dal file di test &nbsp;·&nbsp; <strong>CAL</strong> = valore calcolato dal motore</li>
                <li>Le celle in verde indicano corrispondenza; quelle in rosso indicano una discrepanza.</li>
                <li>I test marcati <em>Fallimento atteso</em> verificano che il sistema rifiuti correttamente matricole o corsi non validi.</li>
            </ul>
        </div>

        <!-- PANNELLO TEST -->
        <div class="panel">

            <div class="test-controls">
                <p class="panel-title">Batteria di unit test</p>
                <button id="btnStartTest" class="btn-run">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="5 3 19 12 5 21 5 3"/>
                    </svg>
                    Avvia i test
                </button>
            </div>

            <!-- Summary (appare dopo run) -->
            <div id="summary-container" class="hidden">
                <div class="summary-bar">
                    <div class="summary-stat stat-total">
                        <span>Totale</span>
                        <strong id="s-total">—</strong>
                    </div>
                    <div class="summary-stat stat-pass">
                        <span>Passati</span>
                        <strong id="s-pass">—</strong>
                    </div>
                    <div class="summary-stat stat-fail">
                        <span>Falliti</span>
                        <strong id="s-fail">—</strong>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="test-table">
                    <thead>
                    <tr>
                        <th style="width:96px;">Matricola</th>
                        <th style="width:170px;">Nominativo</th>
                        <th style="width:110px;">Media</th>
                        <th style="width:140px;">CFU Media / Tot.</th>
                        <th style="width:90px;">Bonus</th>
                        <th style="width:120px;">Media INF</th>
                        <th style="width:96px;">Esito</th>
                    </tr>
                    </thead>
                    <tbody id="test-body">
                    <tr>
                        <td colspan="7" class="empty-state">
                            Pronto. Clicca "Avvia i test" per interrogare il motore di calcolo.
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div id="statusBox" class="status-msg hidden"></div>
        </div>

        <div>
            <a href="../index.php" class="back-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Torna alla Dashboard Operativa
            </a>
        </div>

    </section>
</main>

<script src="<?php echo get_stylesheet_directory_uri(); ?>/js/test_script.js"></script>
</body>
</html>