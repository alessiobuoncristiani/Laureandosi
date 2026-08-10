<?php

require_once __DIR__ . '/../lib/fpdf184/fpdf.php';
require_once 'ProspettoLaureando.php';
require_once 'GestoreFileSystem.php';
require_once 'FileConfigurazione.php';

class GeneratorePDFLaureando
{
    public static function genera(ProspettoLaureando $prospetto, string $dataAppello, FileConfigurazione $config): string
    {
        $pdf = new FPDF();
        self::costruisciPagina($pdf, $prospetto, $dataAppello, $config, false);

        $carriera = $prospetto->getCarriera();
        $pathCartella = GestoreFileSystem::getPercorsoCartella($carriera->getCdl());
        $pathSalvataggio = $pathCartella . $carriera->getMatricola() . '.pdf';
        $pdf->Output('F', $pathSalvataggio);

        $pathMetadata = $pathCartella . $carriera->getMatricola() . '.json';
        $metadati = ['email' => $carriera->getEmail()];
        file_put_contents($pathMetadata, json_encode($metadati, JSON_PRETTY_PRINT));

        return $pathSalvataggio;
    }

    public static function costruisciPagina(FPDF $pdf, ProspettoLaureando $prospetto, string $dataAppello, FileConfigurazione $config, bool $mostraSimulazione = false): void
    {
        $carriera = $prospetto->getCarriera();
        $matricola = $carriera->getMatricola();
        $cdl = $carriera->getCdl();
        $valoriExtra = $prospetto->getValoriAggiuntivi();

        $isInf = isset($valoriExtra['Media pesata esami INF']);

        $maxCfu = $config->getCfuCurriculari($cdl);
        if ($isInf && $maxCfu === 180) $maxCfu = 177;
        $formula = $config->getFormulaVoto($cdl);
        $esamiInf = $isInf ? $config->getEsamiInf() : [];

        $pdf->SetMargins(11, 8);
        $pdf->AddPage();
        $font = 'Arial';

        $pdf->SetFont($font, '', 13);
        $pdf->Cell(0, 6, $cdl, 0, 1, 'C');
        $pdf->Cell(0, 6, 'CARRIERA E SIMULAZIONE DEL VOTO DI LAUREA', 0, 1, 'C');
        $pdf->Ln(3);

        $pdf->SetFont($font, '', 9);
        $altezzaBoxAnagrafica = $isInf ? 33 : 27.5;
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), $pdf->GetPageWidth() - 22, $altezzaBoxAnagrafica);

        $pdf->Cell(45, 5.5, 'Matricola:', 0, 0);
        $pdf->Cell(0, 5.5, $matricola, 0, 1);
        $pdf->Cell(45, 5.5, 'Nome:', 0, 0);
        $pdf->Cell(0, 5.5, $carriera->getNome(), 0, 1);
        $pdf->Cell(45, 5.5, 'Cognome:', 0, 0);
        $pdf->Cell(0, 5.5, $carriera->getCognome(), 0, 1);
        $pdf->Cell(45, 5.5, 'Email:', 0, 0);
        $pdf->Cell(0, 5.5, $carriera->getEmail(), 0, 1);
        $pdf->Cell(45, 5.5, 'Data:', 0, 0);
        $pdf->Cell(0, 5.5, $dataAppello, 0, 1);

        //Se studente di Ing. Ing stampiamo i valori aggiuntivi
        if ($isInf) {
            $pdf->Cell(45, 5.5, 'Bonus:', 0, 0);
            $pdf->Cell(0, 5.5, $valoriExtra['Bonus in Corso applicato'] ?? 'NO', 0, 1);
        }
        $pdf->Ln(3);

        $wEsame = $pdf->GetPageWidth() - 22 - ($isInf ? 44 : 33);
        $pdf->Cell($wEsame, 5.5, 'ESAME', 1, 0, 'C');
        $pdf->Cell(11, 5.5, 'CFU', 1, 0, 'C');
        $pdf->Cell(11, 5.5, 'VOT', 1, 0, 'C');
        $pdf->Cell(11, 5.5, 'MED', 1, 0, 'C');
        if ($isInf) $pdf->Cell(11, 5.5, 'INF', 1, 0, 'C');
        $pdf->Ln();

        $pdf->SetFont($font, '', 8);
        $cfuCurriculari = 0; $cfuMedia = 0;

        foreach ($carriera->getEsami() as $esame) {
            if (!$esame->isInCarriera()) continue;

            $nomeBreve = substr($esame->getNome(), 0, 65);
            $pdf->Cell($wEsame, 4.5, $nomeBreve, 1, 0);
            $pdf->Cell(11, 4.5, $esame->getCfu(), 1, 0, 'C');

            $votoStr = $esame->getVoto() === null ? '0' : (string)$esame->getVoto();
            if ($esame->hasLode()) $votoStr = "30L";

            $pdf->Cell(11, 4.5, $votoStr, 1, 0, 'C');
            $pdf->Cell(11, 4.5, $esame->faMedia() ? 'X' : '', 1, 0, 'C');

            //Se studente di Ing. Ing stampiamo la colonna degli esami informatici
            if ($isInf) {
                $isInfExam = in_array(strtoupper($esame->getNome()), $esamiInf);
                $pdf->Cell(11, 4.5, $isInfExam ? 'X' : '', 1, 0, 'C');
            }
            $pdf->Ln();

            if (!$esame->isSovrannumerario()) $cfuCurriculari += $esame->getCfu();
            if ($esame->faMedia()) $cfuMedia += $esame->getCfu();
        }
        $pdf->Ln(3);

        $pdf->SetFont($font, '', 9);
        $altezzaBoxRiepilogo = $isInf ? 33 : 22;
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), $pdf->GetPageWidth() - 22, $altezzaBoxRiepilogo);

        $pdf->Cell(80, 5.5, 'Media Pesata (M):', 0, 0);
        $pdf->Cell(0, 5.5, number_format($prospetto->getMediaBase(), 3), 0, 1);
        $pdf->Cell(80, 5.5, 'Crediti che fanno media (CFU):', 0, 0);
        $pdf->Cell(0, 5.5, $cfuMedia, 0, 1);
        $pdf->Cell(80, 5.5, 'Crediti curriculari conseguiti:', 0, 0);
        $pdf->Cell(0, 5.5, $cfuCurriculari . '/' . $maxCfu, 0, 1);

        if ($isInf) {
            $pdf->Cell(80, 5.5, 'Voto di tesi (T):', 0, 0);
            $pdf->Cell(0, 5.5, '0', 0, 1);
        }

        $pdf->Cell(80, 5.5, 'Formula calcolo voto di laurea:', 0, 0);
        $pdf->Cell(0, 5.5, $formula, 0, 1);

        if ($isInf) {
            $pdf->Cell(80, 5.5, 'Media pesata esami INF:', 0, 0);
            $pdf->Cell(0, 5.5, number_format($valoriExtra['Media pesata esami INF'], 3), 0, 1);
        }

        if ($mostraSimulazione) {
            self::aggiungiTabellaSimulazione($pdf, $prospetto, $config, $cdl);
        }
    }

    private static function aggiungiTabellaSimulazione(FPDF $pdf, ProspettoLaureando $prospetto, FileConfigurazione $config, string $cdl): void
    {
        $simulazione = $prospetto->getTabellaSimulazione();
        if (empty($simulazione)) return;

        $parC = $config->getParametriC($cdl);
        $parT = $config->getParametriT($cdl);

        $pdf->Ln(3);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(($pdf->GetPageWidth() - 22), 5.5, "SIMULAZIONE DI VOTO DI LAUREA", 1, 1, "C");

        $stepC = (float)($parC['step'] ?? 0);
        $titoloColonna = ($stepC > 0) ? "VOTO COMMISSIONE" : "VOTO TESI";
        $nCell = count($simulazione);

        //  DISEGNO DELLA TABELLA
        if ($nCell <= 10) {
            $pdf->Cell(($pdf->GetPageWidth() - 22) / 2, 5.5, $titoloColonna, 1, 0, "C");
            $pdf->Cell(($pdf->GetPageWidth() - 22) / 2, 5.5, "VOTO DI LAUREA", 1, 1, "C");
            foreach ($simulazione as $riga) {
                $pdf->Cell(($pdf->GetPageWidth() - 22) / 2, 5.5, '+' . $riga['incremento'], 1, 0, "C");
                $pdf->Cell(($pdf->GetPageWidth() - 22) / 2, 5.5, number_format($riga['voto_finale'], 3), 1, 1, "C");
            }
        } else {
            $wQuarto = ($pdf->GetPageWidth() - 22) / 4;
            $pdf->Cell($wQuarto, 5.5, $titoloColonna, 1, 0, "C");
            $pdf->Cell($wQuarto, 5.5, "VOTO DI LAUREA", 1, 0, "C");
            $pdf->Cell($wQuarto, 5.5, $titoloColonna, 1, 0, "C");
            $pdf->Cell($wQuarto, 5.5, "VOTO DI LAUREA", 1, 1, "C");

            $half = (int)ceil($nCell / 2);
            for ($i = 0; $i < $half; $i++) {
                $rigaSx = $simulazione[$i];
                $pdf->Cell($wQuarto, 5.5, '+' . $rigaSx['incremento'], 1, 0, "C");
                $pdf->Cell($wQuarto, 5.5, number_format($rigaSx['voto_finale'], 3), 1, 0, "C");

                if (isset($simulazione[$i + $half])) {
                    $rigaDx = $simulazione[$i + $half];
                    $pdf->Cell($wQuarto, 5.5, '+' . $rigaDx['incremento'], 1, 0, "C");
                    $pdf->Cell($wQuarto, 5.5, number_format($rigaDx['voto_finale'], 3), 1, 1, "C");
                } else {
                    $pdf->Cell($wQuarto, 5.5, "", 1, 0, "C");
                    $pdf->Cell($wQuarto, 5.5, "", 1, 1, "C");
                }
            }
        }
        $pdf->Ln(3);

        // 1. Prende il messaggio dal JSON
        $messaggio = $config->getMessaggioProspetto($cdl);

        // 2. Capisce quale parametro è inattivo (ha lo step a 0),
        // perché è quello di cui dobbiamo stampare MIN e MAX.
        if ($stepC > 0) {
            $paramInattivo = $parT;
        } else {
            $paramInattivo = $parC;
        }

        // 3. Sostituzione delle stringhe 'MIN' e 'MAX' con i valori veri
        $messaggio = str_replace(
            ['MIN', 'MAX'],
            [$paramInattivo['min'], $paramInattivo['max']],
            $messaggio
        );

        $pdf->MultiCell(0, 4, $messaggio);
    }
}
