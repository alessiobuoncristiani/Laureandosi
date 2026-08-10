<?php
// Nascondiamo gli errori grezzi per non corrompere il JSON
ini_set('display_errors', 0);
set_time_limit(0);
header('Content-Type: application/json; charset=utf-8');

// Gestione CORSI
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Funzioni Helper
function inviaRisposta(bool $success, string $message, string $link = ''): void {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'link' => $link
    ]);
    exit();
}

function inviaErrore(int $codiceHttp, string $messaggio): void {
    http_response_code($codiceHttp);
    inviaRisposta(false, $messaggio);
}

// Intercettatore di crash fatali
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        inviaErrore(500, 'CRASH PHP: ' . $error['message'] . ' in ' . basename($error['file']) . ' alla riga ' . $error['line']);
    }
});

// INIZIO LOGICA
try {
    require_once __DIR__ . '/classi/InterfacciaGrafica.php';

    $azione = $_POST['azione'] ?? '';
    $cdl = trim($_POST['cdl'] ?? '');
    $dataAppello = trim($_POST['data'] ?? '');
    $matricole = trim($_POST['matricole'] ?? '');

    if (empty($azione)) {
        inviaErrore(400, "Azione non specificata.");
    }

    $ui = new InterfacciaGrafica($cdl, $dataAppello, $matricole);

    switch ($azione) {
        case 'crea':
            $ui->generaProspetti();
            inviaRisposta(true, "Prospetti generati con successo!");
            break;

        case 'apri':
            $linkPdf = $ui->apriProspetti();
            inviaRisposta(true, "Apertura file in corso...", $linkPdf);
            break;

        case 'lista_invio':
            $lista = $ui->getProspettiDaInviare();
            inviaRisposta(true, "Lista recuperata", json_encode($lista));
            break;

        case 'invia_singolo':
            $mat = trim($_POST['matricola_singola'] ?? '');
            $ui->inviaProspettoSingolo($mat);
            inviaRisposta(true, "Inviato");
            break;

        case 'check':
            $esiste = $ui->esistonoProspetti();
            $daInviare = $ui->esistonoProspettiDaInviare();

            echo json_encode([
                'success' => true,
                'message' => 'Check completato',
                'apri'    => $esiste,    // true se c'è il PDF commissione
                'invia'   => $daInviare  // true se ci sono PDF studenti da inviare
            ]);
            exit();

        default:
            inviaErrore(400, "Azione '$azione' non riconosciuta dal sistema.");
    }

} catch (Exception $e) {
    // Intercettiamo le eccezioni lanciate dal backend
    inviaErrore(500, "Errore: " . $e->getMessage());
}