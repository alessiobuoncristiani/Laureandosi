<?php

require_once 'FileConfigurazione.php';
require_once 'GestoreFileSystem.php';
require_once 'GestioneCarrieraStudente.php';
require_once 'CalcolatoreVotoLaurea.php';
require_once 'CalcolatoreVotoInformatica.php';
require_once 'GeneratorePDFLaureando.php';
require_once 'GeneratorePDFCommissione.php';
require_once 'GestoreEmail.php';

class InterfacciaGrafica
{
    private string $cdl;
    private string $dataAppello;
    private string $matricoleRaw;
    private FileConfigurazione $fileConf;

    public function __construct(string $cdl, string $dataAppello = '', string $matricoleRaw = '')
    {
        $this->cdl = $cdl;
        $this->dataAppello = $dataAppello;
        $this->matricoleRaw = $matricoleRaw;
        $this->fileConf = FileConfigurazione::getInstance();
    }

    public function generaProspetti(): void
    {
        $matricoleArray = preg_split('/\s+/', trim($this->matricoleRaw), -1, PREG_SPLIT_NO_EMPTY);

        if (empty($matricoleArray)) {
            throw new Exception("Nessuna matricola inserita.");
        }

        //Controlliamo che le matricole siano valide
        $carriereValidate = [];
        foreach ($matricoleArray as $matricola) {
            $carriereValidate[] = GestioneCarrieraStudente::prelevaCarriera($matricola, $this->cdl, $this->dataAppello, $this->fileConf);
        }

        GestoreFileSystem::preparaCartella($this->cdl);
        $listaProspetti = [];

        foreach ($carriereValidate as $carriera) {
            if ($this->cdl === "T. Ing. Informatica") {
                $calcolatore = new CalcolatoreVotoInformatica();
            } else {
                $calcolatore = new CalcolatoreVotoLaurea();
            }

            $prospetto = $calcolatore->creaProspetto($carriera, $this->fileConf);
            $listaProspetti[] = $prospetto;

            GeneratorePDFLaureando::genera($prospetto, $this->dataAppello, $this->fileConf);
        }

        GeneratorePDFCommissione::genera($listaProspetti, $this->cdl, $this->dataAppello, $this->fileConf);
    }

    public function getProspettiDaInviare(): array
    {
        $cartella = GestoreFileSystem::getPercorsoCartella($this->cdl);
        $filesPdf = glob($cartella . '*.pdf');
        $matricole = [];

        foreach ($filesPdf as $f) {
            if (strpos($f, 'Prospetto_Commissione') === false) {
                $matricole[] = basename($f, '.pdf');
            }
        }
        return $matricole;
    }

    public function inviaProspettoSingolo(string $matricola): void
    {
        $cartella = GestoreFileSystem::getPercorsoCartella($this->cdl);
        $pathPdf = $cartella . $matricola . '.pdf';
        $pathMeta = $cartella . $matricola . '.json';

        if (!file_exists($pathPdf) || !file_exists($pathMeta)) {
            throw new Exception("File mancanti per la matricola $matricola");
        }

        $mailText = $this->fileConf->getEmailBody();
        $mailObj = $this->fileConf->getEmailSubject($this->cdl);

        $gestoreEmail = new GestoreEmail($mailObj, $mailText, 0);

        $metadati = json_decode(file_get_contents($pathMeta), true);
        $successo = $gestoreEmail->inviaEmailConAllegato($metadati['email'], $pathPdf, $this->fileConf);

        if ($successo) {
            unlink($pathPdf);
            unlink($pathMeta);
        } else {
            throw new Exception("Errore critico durante l'invio per lo studente: $matricola");
        }
    }

    public function apriProspetti(): string
    {
        $pathCartella = GestoreFileSystem::getPercorsoCartella($this->cdl);
        $fileCommissione = $pathCartella . 'Prospetto_Commissione.pdf';

        if (!file_exists($fileCommissione)) {
            throw new Exception("Non è stato generato alcun prospetto per il CdL: " . $this->cdl);
        }

        $nomeCartellaSicuro = preg_replace('/[^a-zA-Z0-9]/', '_', $this->cdl);
        $timestamp = time();

        return "Prospetti_Correnti/" . rawurlencode($nomeCartellaSicuro) . "/Prospetto_Commissione.pdf?v=" . $timestamp;
    }

    public function esistonoProspetti(): bool
    {
        if (empty($this->cdl)) {
            return false;
        }
        $pathCartella = GestoreFileSystem::getPercorsoCartella($this->cdl);
        return file_exists($pathCartella . 'Prospetto_Commissione.pdf');
    }
    public function esistonoProspettiDaInviare(): bool
    {
        return count($this->getProspettiDaInviare()) > 0;
    }
}
