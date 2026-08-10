<?php
$percorsoJson = __DIR__ . '/config/cdl.json';
$corsiDisponibili = [];
if (file_exists($percorsoJson)) {
    $jsonData = json_decode(file_get_contents($percorsoJson), true);
    $datiDaCiclare = isset($jsonData['corsi']) ? $jsonData['corsi'] : $jsonData;
    foreach ($datiDaCiclare as $sigla => $dati) {
        $nomeCdl = is_array($dati) && isset($dati['cdl-alt']) ? $dati['cdl-alt'] : $sigla;
        $corsiDisponibili[$sigla] = $nomeCdl;
    }
}

if (isset($_GET['test'])) {
    require_once __DIR__ . '/test/test.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Prospetti di Laurea</title>
    <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/style.css">
    <script>const themeUrl = "<?php echo get_stylesheet_directory_uri(); ?>";</script>
</head>
<body>

<main class="dashboard">

    <header class="dash-header">
        <div class="header-inner">
            <div class="logo-area">
                <div class="logo-icon">🎓</div>
                <div>
                    <h1>Gestione Prospetti di Laurea</h1>
                    <p>Unità Didattica DII — Università di Pisa</p>
                </div>
            </div>
            <div class="session-badge">
                <span class="session-dot"></span>
                <span>Sessione:&nbsp;</span>
                <strong id="badge-cdl">nessun corso selezionato</strong>
            </div>
        </div>
    </header>

    <section class="dash-content">

        <!-- PANNELLO DATI -->
        <div class="panel data-panel">
            <p class="panel-title">Dati dell'Appello</p>

            <div class="input-group">
                <label for="cdl">Corso di Laurea</label>
                <select id="cdl" name="cdl">
                    <option value="" disabled selected>— Seleziona un corso —</option>
                    <?php foreach ($corsiDisponibili as $sigla => $nomeCompleto): ?>
                        <option value="<?= htmlspecialchars($sigla) ?>"><?= htmlspecialchars($nomeCompleto) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="input-group">
                <label for="data-laurea">Data dell'Appello</label>
                <input type="date" id="data-laurea" name="data_laurea">
            </div>

            <div class="input-group" style="flex-grow: 1;">
                <label for="matricole">
                    Matricole Laureandi
                    <span class="badge-hint">una per riga o separate da spazio</span>
                </label>
                <textarea id="matricole" placeholder="Es.&#10;123456&#10;987654&#10;..."></textarea>
            </div>
        </div>

        <!-- PANNELLO AZIONI -->
        <div class="panel action-panel">
            <p class="panel-title">Operazioni</p>

            <div class="action-buttons">
                <button class="btn btn-primary" onclick="eseguiAzione('crea')">
                    <span class="btn-step">1</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="12" y1="18" x2="12" y2="12"/>
                        <line x1="9" y1="15" x2="15" y2="15"/>
                    </svg>
                    Genera Prospetti
                </button>

                <div class="btn-group-divider">poi</div>

                <button class="btn btn-secondary" id="btn-apri" onclick="eseguiAzione('apri')" disabled>
                    <span class="btn-step">2</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    Apri Prospetti
                </button>

                <button class="btn btn-success" id="btn-invia" onclick="eseguiAzione('invia')" disabled>
                    <span class="btn-step">3</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"/>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                    Invia Email ai Laureandi
                </button>
            </div>

            <div id="progress-container" class="progress-box hidden">
                <div class="progress-track">
                    <div id="progress-bar-fill" class="progress-fill"></div>
                </div>
                <span id="progress-text">Attendere…</span>
            </div>

            <div id="statusBox" class="status-msg hidden"></div>
        </div>

    </section>
</main>

<script src="<?php echo get_stylesheet_directory_uri(); ?>/js/script.js"></script>
</body>
</html>