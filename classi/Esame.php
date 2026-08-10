<?php

class Esame
{
    private string $nome;
    private string $codice;
    private int $cfu;
    private ?int $voto;
    private bool $lode;
    private bool $sovran_flag;

    // Proprietà dinamiche gestite dal Calcolatore
    private bool $faMediaModificata = true;
    private bool $inCarriera = true;

    public function __construct(string $nome, string $codice, int $cfu, ?int $voto, bool $lode, bool $sovran_flag)
    {
        $this->nome = $nome;
        $this->codice = $codice;
        $this->cfu = $cfu;
        $this->voto = $voto;
        $this->lode = $lode;
        $this->sovran_flag = $sovran_flag;
    }

    public function getNome(): string { return $this->nome; }
    public function getCodice(): string { return $this->codice; }
    public function getCfu(): int { return $this->cfu; }
    public function getVoto(): ?int { return $this->voto; }
    public function hasLode(): bool { return $this->lode; }
    public function isSovrannumerario(): bool { return $this->sovran_flag; }

    // SETTERS PER IL CALCOLATORE
    public function setFaMedia(bool $stato): void { $this->faMediaModificata = $stato; }
    public function setInCarriera(bool $stato): void { $this->inCarriera = $stato; }

    // GETTERS PER LA LOGICA
    public function faMedia(): bool
    {
        return ($this->voto !== null && $this->voto > 0 && !$this->sovran_flag && $this->faMediaModificata);
    }

    public function isInCarriera(): bool
    {
        return $this->inCarriera;
    }
}