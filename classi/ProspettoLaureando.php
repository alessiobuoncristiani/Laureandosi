<?php

require_once 'CarrieraLaureando.php';

class ProspettoLaureando
{
    private CarrieraLaureando $carriera;
    private float $mediaBase;
    private float $votoPartenza;
    private array $valoriAggiuntivi;
    private array $tabellaSimulazione;

    public function __construct(
        CarrieraLaureando $carriera,
        float $mediaBase,
        float $votoPartenza,
        array $valoriAggiuntivi = [],
        array $tabellaSimulazione = []
    ) {
        $this->carriera = $carriera;
        $this->mediaBase = $mediaBase;
        $this->votoPartenza = $votoPartenza;
        $this->valoriAggiuntivi = $valoriAggiuntivi;
        $this->tabellaSimulazione = $tabellaSimulazione;
    }

    public function getCarriera(): CarrieraLaureando { return $this->carriera; }
    public function getMediaBase(): float {
        return $this->mediaBase;
        return $this->mediaBase;
    }
    public function getVotoPartenza(): float {
        return $this->votoPartenza;
    }
    public function getValoriAggiuntivi(): array {
        return $this->valoriAggiuntivi;
    }
    public function getTabellaSimulazione(): array {
        return $this->tabellaSimulazione;
    }
}