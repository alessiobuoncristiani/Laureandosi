<?php

require_once __DIR__ . '/CarrieraLaureando.php';
require_once __DIR__ . '/Esame.php';
require_once __DIR__ . '/FileConfigurazione.php';

class GestioneCarrieraStudente
{
    private const BASE_PATH = __DIR__ . '/../dati_laureandi/';

    public static function prelevaCarriera(string $matricola, ?string $cdlAtteso, ?string $dataAppello, FileConfigurazione $config): CarrieraLaureando
    {
        $pathEsami = self::BASE_PATH . $matricola . '_esami.json';
        if (!file_exists($pathEsami)) throw new Exception("Carriera non trovata sul server per la matricola: $matricola");

        $datiEsami = json_decode(file_get_contents($pathEsami), true);
        if ($datiEsami === null) throw new Exception("File JSON Esami malformato per matricola: $matricola");

        $listaEsamiRaw = $datiEsami['Esami']['Esame'] ?? [];
        if (empty($listaEsamiRaw)) throw new Exception("La carriera della matricola $matricola è vuota.");

        $primoEsame = reset($listaEsamiRaw);

        // INIZIO BLOCCO DI SICUREZZA CdL
        $corsoReale = (isset($primoEsame['CORSO']) && is_scalar($primoEsame['CORSO'])) ? trim((string)$primoEsame['CORSO']) : 'Sconosciuto';

        if ($cdlAtteso !== null) {
            $nomeRealeAtteso = $config->getNomeAlternativoCdl($cdlAtteso);
            if ($nomeRealeAtteso !== '' && strtoupper($corsoReale) !== strtoupper($nomeRealeAtteso)) {
                throw new Exception("INCONGRUENZA: La matricola $matricola è iscritta a '$corsoReale', ma stai generando un prospetto per '$cdlAtteso'.");
            }
            $cdl = $cdlAtteso;
        } else {
            $cdl = $corsoReale;
        }
        // FINE BLOCCO DI SICUREZZA

        usort($listaEsamiRaw, function($a, $b) {
            $annoA = (int)($a['ANNO_CORSO'] ?? 99);
            $annoB = (int)($b['ANNO_CORSO'] ?? 99);
            return $annoA <=> $annoB;
        });

        $annoImm = (isset($primoEsame['ANNO_IMM']) && is_scalar($primoEsame['ANNO_IMM'])) ? (int)$primoEsame['ANNO_IMM'] : 0;

        $pathAnag = self::BASE_PATH . $matricola . '_anagrafica.json';
        if (!file_exists($pathAnag)) throw new Exception("Anagrafica non trovata per la matricola: $matricola");

        $datiAnag = json_decode(file_get_contents($pathAnag), true);
        if ($datiAnag === null) throw new Exception("File JSON Anagrafica malformato per matricola: $matricola");

        $entry = $datiAnag['Entries']['Entry'] ?? [];

        $nome = (isset($entry['nome']) && is_scalar($entry['nome'])) ? trim((string)$entry['nome']) : 'Nome Sconosciuto';
        $cognome = (isset($entry['cognome']) && is_scalar($entry['cognome'])) ? trim((string)$entry['cognome']) : 'Cognome Sconosciuto';
        $email = (isset($entry['email_ate']) && is_scalar($entry['email_ate'])) ? trim((string)$entry['email_ate']) : 'email.mancante@studenti.unipi.it';

        $laureando = new CarrieraLaureando($matricola, $nome, $cognome, $email, $cdl, $annoImm);

        // Controllo Date
        $parsedInizio = strtotime(str_replace('/', '-', $primoEsame['INIZIO_CARRIERA'] ?? ''));
        $timestampInizio = ($parsedInizio !== false) ? $parsedInizio : null;

        $dataFineGrezza = $dataAppello ?: date('Y-m-d');
        $parsedLaurea = strtotime($dataFineGrezza);
        $timestampLaurea = ($parsedLaurea !== false) ? $parsedLaurea : time();

        $laureando->setTimestamps($timestampInizio, $timestampLaurea);

        foreach ($listaEsamiRaw as $e) {
            $des = (isset($e['DES']) && is_scalar($e['DES'])) ? trim((string)$e['DES']) : '';
            $cod = (isset($e['COD']) && is_scalar($e['COD'])) ? trim((string)$e['COD']) : '';

            if ($des === '' || $cod === '') continue;

            $votoRaw = (isset($e['VOTO']) && is_scalar($e['VOTO'])) ? trim((string)$e['VOTO']) : '';
            $voto = ($votoRaw === '' || strtolower($votoRaw) === 'null') ? null : (int)$votoRaw;
            $lode = (stripos($votoRaw, 'lode') !== false || strtoupper(trim($votoRaw)) === '30L');
            $peso = (isset($e['PESO']) && is_scalar($e['PESO'])) ? (int)$e['PESO'] : 0;
            $sovrannumerario = (isset($e['SOVRAN_FLG']) && $e['SOVRAN_FLG'] == 1);

            $laureando->aggiungiEsame(new Esame($des, $cod, $peso, $voto, $lode, $sovrannumerario));
        }

        return $laureando;
    }
}