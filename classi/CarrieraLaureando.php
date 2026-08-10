<?php

require_once __DIR__ . '/Esame.php';

class CarrieraLaureando
{
    private string $matricola;
    private string $nome;
    private string $cognome;
    private string $email;
    private string $cdl;
    private int $annoImmatricolazione;
    private array $esami = [];

    // Proprietà per il calcolo temporale del bonus
    private ?int $timestampIscrizione = null;
    private int $timestampLaurea;

    public function __construct(string $matricola, string $nome, string $cognome, string $email, string $cdl, int $annoImmatricolazione)
    {
        $this->matricola = $matricola;
        $this->nome = $nome;
        $this->cognome = $cognome;
        $this->email = $email;
        $this->cdl = $cdl;
        $this->annoImmatricolazione = $annoImmatricolazione;
        $this->timestampLaurea = time(); // Di default imposta il momento corrente
    }

    public function aggiungiEsame(Esame $esame): void
    {
        $this->esami[] = $esame;
    }

    // GETTERS PER IL PDF E PER IL CALCOLATORE
    public function getMatricola(): string { return $this->matricola; }
    public function getNome(): string { return $this->nome; }
    public function getCognome(): string { return $this->cognome; }
    public function getNomeCompleto(): string { return $this->cognome . ' ' . $this->nome; }
    public function getEmail(): string { return $this->email; }
    public function getCdl(): string { return $this->cdl; }
    public function getAnnoImmatricolazione(): int { return $this->annoImmatricolazione; }
    public function getEsami(): array { return $this->esami; }

    // SETTERS E GETTERS PER IL BONUS TEMPORALE (3.6 ANNI)
    public function setTimestamps(?int $timestampIscrizione, int $timestampLaurea): void
    {
        $this->timestampIscrizione = $timestampIscrizione;
        $this->timestampLaurea = $timestampLaurea;
    }

    public function getDataIscrizione(): ?int { return $this->timestampIscrizione; }
    public function getDataLaurea(): int { return $this->timestampLaurea; }
}