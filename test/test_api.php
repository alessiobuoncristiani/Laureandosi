<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);

require_once __DIR__ . '/../classi/FileConfigurazione.php';
require_once __DIR__ . '/../classi/GestioneCarrieraStudente.php';
require_once __DIR__ . '/../classi/CalcolatoreVotoLaurea.php';
require_once __DIR__ . '/../classi/CalcolatoreVotoInformatica.php';

function confrontaFloat($val1, $val2) {
    return abs(floatval($val1) - floatval($val2)) < 0.001;
}

try {
    $percorsoJsonTest = __DIR__ . '/../config/TestExpectedOutput.json';
    if (!file_exists($percorsoJsonTest)) {
        throw new Exception("File TestExpectedOutput.json non trovato.");
    }

    $datiTest = json_decode(file_get_contents($percorsoJsonTest), true);

    $config = FileConfigurazione::getInstance();
    $risultati = [];

    $dataLaureaTest = $datiTest['dataLaurea'] ?? null;

    foreach ($datiTest['matricole'] as $matr => $testData) {
        $riga = [
            'matricola' => $matr,
            'cdl' => $testData['cdl'],
            'nomeAtteso' => $testData['nome'] . " " . $testData['cognome'],
            'nomeReale' => '-',
            'dati' => [],
            'esito' => false,
            'errore' => ''
        ];

        if (isset($testData['shouldFail']) && $testData['shouldFail'] === true) {
            try {
                $carriera = GestioneCarrieraStudente::prelevaCarriera($matr, $testData['cdl'], $dataLaureaTest, $config);

                $config->getFormulaVoto($testData['cdl']);

                $riga['errore'] = "Doveva fallire ma è stato trovato!";
            } catch (Exception $e) {
                $riga['esito'] = true;
                $riga['errore'] = "Fallito correttamente (Eccezione: " . $e->getMessage() . ")";
            }
            $risultati[] = $riga;
            continue;
        }

        try {
            $carriera = GestioneCarrieraStudente::prelevaCarriera($matr, $testData['cdl'], $dataLaureaTest, $config);
            $riga['nomeReale'] = $carriera->getNome() . " " . $carriera->getCognome();

            $calcolatore = ($testData['cdl'] === "T. Ing. Informatica") ? new CalcolatoreVotoInformatica() : new CalcolatoreVotoLaurea();
            $prospetto = $calcolatore->creaProspetto($carriera, $config);

            $mediaCalc = $prospetto->getMediaBase();
            $valoriExtra = $prospetto->getValoriAggiuntivi();

            $cfuMediaCalc = 0; $cfuTotCalc = 0;
            foreach ($carriera->getEsami() as $esame) {
                if ($esame->isInCarriera()) {
                    if (!$esame->isSovrannumerario()) $cfuTotCalc += $esame->getCfu();
                    if ($esame->faMedia()) $cfuMediaCalc += $esame->getCfu();
                }
            }

            $bonusCalc = (isset($valoriExtra['Bonus in Corso applicato']) && $valoriExtra['Bonus in Corso applicato'] === 'SI');
            $mediaInfCalc = $valoriExtra['Media pesata esami INF'] ?? 0;

            $riga['dati'] = [
                'mediaAttesa' => $testData['expected']['media'],
                'mediaCalc' => round($mediaCalc, 3),
                'cfuMediaAttesi' => $testData['expected']['cfuMedia'],
                'cfuMediaCalc' => $cfuMediaCalc,
                'cfuTotAttesi' => $testData['expected']['cfuTotali'],
                'cfuTotCalc' => $cfuTotCalc,
                'bonusAtteso' => $testData['expected']['bonus'] ?? null,
                'bonusCalc' => $bonusCalc,
                'mediaInfAttesa' => $testData['expected']['mediaInf'] ?? null,
                'mediaInfCalc' => round($mediaInfCalc, 3)
            ];

            $isPassed = true;
            if ($riga['nomeReale'] !== $riga['nomeAtteso']) $isPassed = false;
            if (!confrontaFloat($mediaCalc, $testData['expected']['media'])) $isPassed = false;
            if ($cfuMediaCalc != $testData['expected']['cfuMedia']) $isPassed = false;
            if ($cfuTotCalc != $testData['expected']['cfuTotali']) $isPassed = false;

            if ($testData['cdl'] === "T. Ing. Informatica") {
                if ($bonusCalc !== $testData['expected']['bonus']) $isPassed = false;
                if (!confrontaFloat($mediaInfCalc, $testData['expected']['mediaInf'])) $isPassed = false;
            }

            $riga['esito'] = $isPassed;

        } catch (Exception $e) {
            $riga['errore'] = "Errore: " . $e->getMessage();
        }

        $risultati[] = $riga;
    }

    echo json_encode(['success' => true, 'risultati' => $risultati]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}