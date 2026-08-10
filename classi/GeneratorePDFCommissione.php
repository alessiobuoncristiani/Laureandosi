<?php

require_once __DIR__ . '/../lib/fpdf184/fpdf.php';
require_once 'GeneratorePDFLaureando.php';
require_once 'GestoreFileSystem.php';
require_once 'FileConfigurazione.php';

class GeneratorePDFCommissione
{
    public static function genera(array $listaProspetti, string $cdl, string $dataAppello, FileConfigurazione $config): string
    {
        $pdf = new FPDF();
        $font = 'Arial';

        // PAGINA 1: ELENCO RIASSUNTIVO (Con 4 colonne)
        $pdf->SetMargins(11, 8);
        $pdf->AddPage();
        $pdf->SetFont($font, '', 13);
        $pdf->Cell(0, 6, $cdl, 0, 1, 'C');
        $pdf->Cell(0, 6, 'LISTA LAUREANDI', 0, 1, 'C');
        $pdf->Ln(3);

        $pdf->SetFont($font, 'B', 11);
        $wCol = ($pdf->GetPageWidth() - 22) / 4;
        $pdf->Cell($wCol, 7, 'MATRICOLA', 1, 0, 'C');
        $pdf->Cell($wCol, 7, 'NOMINATIVO', 1, 0, 'C');
        $pdf->Cell($wCol, 7, 'VOTO BASE', 1, 0, 'C');
        $pdf->Cell($wCol, 7, 'VOTO LAUREA', 1, 1, 'C');

        $pdf->SetFont($font, '', 10);
        foreach ($listaProspetti as $prospetto) {
            $carriera = $prospetto->getCarriera();

            $pdf->Cell($wCol, 6, $carriera->getMatricola(), 1, 0, 'C');
            $pdf->Cell($wCol, 6, substr($carriera->getNomeCompleto(), 0, 25), 1, 0, 'L');
            $pdf->Cell($wCol, 6, number_format($prospetto->getVotoPartenza(), 3), 1, 0, 'C');
            $pdf->Cell($wCol, 6, "/110", 1, 1, 'C');
        }

        foreach ($listaProspetti as $prospetto) {
            GeneratorePDFLaureando::costruisciPagina($pdf, $prospetto, $dataAppello, $config, true);
        }

        // Salvataggio
        $pathCartella = GestoreFileSystem::getPercorsoCartella($cdl);
        $pathSalvataggio = $pathCartella . 'Prospetto_Commissione.pdf';
        $pdf->Output('F', $pathSalvataggio);

        return $pathSalvataggio;
    }
}