<?php

require_once 'CarrieraLaureando.php';
require_once 'ProspettoLaureando.php';
require_once 'FileConfigurazione.php';

class CalcolatoreVotoLaurea
{
    protected array $valoriAggiuntivi = [];

    public function creaProspetto(CarrieraLaureando $carriera, FileConfigurazione $config): ProspettoLaureando
    {
        $cdl = $carriera->getCdl();
        $matricola = $carriera->getMatricola();

        // Recupero i parametri di calcolo
        $formula = $config->getFormulaVoto($cdl);
        $cfuTotali = $config->getCfuCurriculari($cdl);
        $parT = $config->getParametriT($cdl);
        $parC = $config->getParametriC($cdl);

        $daEscludere = $config->getEsamiNonCarriera($cdl, $matricola);
        $nonMedia = $config->getEsamiNonMedia($cdl, $matricola);

        $this->valoriAggiuntivi = [];

        $esamiFiltrati = $this->applicaFiltriConfigurazione($carriera->getEsami(), $daEscludere, $nonMedia);
        $esamiCalcolabili = $this->applicaRegoleSpeciali($esamiFiltrati, $carriera, $config);

        $mediaBase = $this->calcolaMediaPesata($esamiCalcolabili);

        $votoPartenza = $this->calcolaVotoBase($mediaBase, $formula, $cfuTotali, 0, 0);
        $tabellaPrevisionale = $this->generaTabellaPrevisionale($mediaBase, $formula, $cfuTotali, $parT, $parC);

        return new ProspettoLaureando($carriera, $mediaBase, $votoPartenza, $this->valoriAggiuntivi, $tabellaPrevisionale);
    }

    public function applicaFiltriConfigurazione(array $esami, array $daEscludere, array $nonMedia): array
    {
        foreach ($esami as $esame) {
            $nomeUpp = strtoupper($esame->getNome());
            if (in_array($nomeUpp, $daEscludere)) {
                $esame->setInCarriera(false);
                $esame->setFaMedia(false);
            } elseif (in_array($nomeUpp, $nonMedia)) {
                $esame->setFaMedia(false);
            }
        }
        return $esami;
    }

    protected function applicaRegoleSpeciali(array $esami, CarrieraLaureando $carriera, FileConfigurazione $config): array
    {
        return $esami;
    }

    public function calcolaMediaPesata(array $esami): float
    {
        $somma = 0; $cfu = 0;
        foreach ($esami as $e) {
            if ($e->isInCarriera() && $e->faMedia()) {
                $somma += ($e->getVoto() * $e->getCfu());
                $cfu += $e->getCfu();
            }
        }
        return ($cfu > 0) ? ($somma / $cfu) : 0.0;
    }

    public function calcolaVotoBase(float $media, string $formula, int $cfuTotali, float $valoreT = 0, float $valoreC = 0): float
    {
        $stringaMatematica = str_replace(
            ['M', 'CFU', 'T', 'C'],
            [$media, $cfuTotali, $valoreT, $valoreC],
            strtoupper($formula)
        );

        if (!preg_match('/^[0-9\.\+\-\*\/\(\)\s]+$/', $stringaMatematica)) {
            throw new Exception("Errore di sicurezza: La formula contiene caratteri non validi.");
        }

        try {
            $votoFinale = @eval("return $stringaMatematica;");
            if ($votoFinale === false || !is_numeric($votoFinale)) {
                throw new Exception("Divisione per zero o formula non valida.");
            }
        } catch (Throwable $e) {
            throw new Exception("Errore nel calcolo matematico della formula: " . $formula);
        }

        return min((float)$votoFinale, 110.0);
    }

    public function generaTabellaPrevisionale(float $mediaBase, string $formula, int $cfuTotali, array $parT, array $parC): array
    {
        $simulazione = [];

        if ((float)$parC['step'] > 0) {
            for ($c = (float)$parC['min']; $c <= (float)$parC['max']; $c += (float)$parC['step']) {
                $voto = $this->calcolaVotoBase($mediaBase, $formula, $cfuTotali, 0, $c);
                $simulazione[] = ['incremento' => $c, 'voto_finale' => $voto];
            }
        } elseif ((float)$parT['step'] > 0) {
            for ($t = (float)$parT['min']; $t <= (float)$parT['max']; $t += (float)$parT['step']) {
                $voto = $this->calcolaVotoBase($mediaBase, $formula, $cfuTotali, $t, 0);
                $simulazione[] = ['incremento' => $t, 'voto_finale' => $voto];
            }
        }
        return $simulazione;
    }
}