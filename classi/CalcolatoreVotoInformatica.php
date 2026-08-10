<?php

require_once 'CalcolatoreVotoLaurea.php';

class CalcolatoreVotoInformatica extends CalcolatoreVotoLaurea
{
    protected function applicaRegoleSpeciali(array $esami, CarrieraLaureando $carriera, FileConfigurazione $config): array
    {
        // 1. Controllo il Bonus in Corso (Limite: 3 anni e 6 mesi)
        $timestampInizio = $carriera->getDataIscrizione();
        $timestampLaurea = $carriera->getDataLaurea();

        if ($timestampInizio !== null) {
            $limiteSecondi = strtotime('+3 years 6 months', $timestampInizio);
            $inCorso = $timestampLaurea <= $limiteSecondi;
        } else { //calcolo approssimativo
            $annoLaurea = (int)date('Y', $timestampLaurea);
            if ((int)date('m', $timestampLaurea) <= 4) {
                $annoLaurea -= 1;
            }
            $inCorso = ($annoLaurea - $carriera->getAnnoImmatricolazione()) <= 3;
        }

        // 2. Applico il bonus (SE IN CORSO) prima di calcolare le altre medie
        if ($inCorso) {
            $this->valoriAggiuntivi['Bonus in Corso applicato'] = 'SI';
            $esami = $this->scartaEsamePeggiore($esami);
        } else {
            $this->valoriAggiuntivi['Bonus in Corso applicato'] = 'NO';
        }

        // 3. Calcolo la media speciale per Informatica
        // (Senza l'esame peggiore se è stato scartato)
        $nomiEsamiInf = $config->getEsamiInf();
        $mediaInf = $this->calcolaMediaSpecialeINF($esami, $nomiEsamiInf);
        $this->valoriAggiuntivi['Media pesata esami INF'] = round($mediaInf, 3);

        return $esami;
    }

    private function scartaEsamePeggiore(array $esami): array
    {
        $indicePeggiore = -1;
        $votoMinimo = 31;
        $cfuMassimi = -1;

        foreach ($esami as $index => $esame) {
            $voto = $esame->getVoto();
            $cfu = $esame->getCfu();

            if ($voto === null || $esame->hasLode() || !$esame->faMedia() || !$esame->isInCarriera()) {
                continue; // Saltiamo le lodi e gli esami senza voto
            }

            if ($voto < $votoMinimo) {
                $votoMinimo = $voto;
                $cfuMassimi = $cfu;
                $indicePeggiore = $index;
            } elseif ($voto === $votoMinimo) {
                if ($cfu > $cfuMassimi) {
                    $cfuMassimi = $cfu;
                    $indicePeggiore = $index;
                }
            }
        }

        if ($indicePeggiore !== -1) {
            $esami[$indicePeggiore]->setFaMedia(false);
        }

        return $esami;
    }

    private function calcolaMediaSpecialeINF(array $esami, array $nomiEsamiInf): float
    {
        $somma = 0;
        $cfu = 0;

        foreach ($esami as $e) {
            if ($e->isInCarriera() && $e->faMedia() && in_array(strtoupper($e->getNome()), $nomiEsamiInf)) {
                $somma += ($e->getVoto() * $e->getCfu());
                $cfu += $e->getCfu();
            }
        }

        return ($cfu > 0) ? ($somma / $cfu) : 0.0;
    }
}