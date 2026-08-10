<?php

class FileConfigurazione
{
    private const CONFIG_PATH = __DIR__ . '/../config/';
    private static ?array $rootData = null;
    private static ?array $corsiData = null;
    private static ?array $esamiInfData = null;
    private static ?array $filtriData = null;

    private function __construct()
    {
        self::init();
    }

    private static function init(): void
    {
        if (self::$rootData === null) {
            self::$rootData = self::leggiJson(self::CONFIG_PATH . 'cdl.json');
            self::$corsiData = self::$rootData['corsi'] ?? [];
            self::$esamiInfData = self::leggiJson(self::CONFIG_PATH . 'esami_inf.json');
            self::$filtriData = self::leggiJson(self::CONFIG_PATH . 'filtri.json');
        }
    }

    private static function leggiJson(string $path): array
    {
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            return is_array($data) ? $data : [];
        }
        return [];
    }

    private static ?self $instance = null;

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function getNomeAlternativoCdl(string $cdl): string
    {
        return self::$corsiData[$cdl]['cdl-alt'] ?? '';
    }
    public function getCfuCurriculari(string $cdl): int
    {
        return self::$corsiData[$cdl]['tot-CFU'] ?? 180;
    }

    // Se il corso non c'è, blocca il programma e lancia l'errore!
    public function getFormulaVoto(string $cdl): string
    {
        if (!isset(self::$corsiData[$cdl]['formula-laurea'])) {
            throw new Exception("Errore: Il corso di laurea '$cdl' non è supportato dal sistema.");
        }
        return self::$corsiData[$cdl]['formula-laurea'];
    }

    public function getParametriT(string $cdl): array
    {
        return self::$corsiData[$cdl]['par-T'] ?? ['min' => 0, 'max' => 0, 'step' => 0];
    }

    public function getParametriC(string $cdl): array
    {
        return self::$corsiData[$cdl]['par-C'] ?? ['min' => 0, 'max' => 0, 'step' => 0];
    }

    public function getMessaggioProspetto(string $cdl): string
    {
        return self::$corsiData[$cdl]['nota-finale'] ?? "I calcoli tengono conto dei filtri di legge.";
    }
    public function getEmailHost(): string
    {
        return self::$rootData['email']['host'] ?? 'mixer.unipi.it';
    }

    public function getEmailFromName(): string
    {
        return self::$rootData['email']['from-name'] ?? 'Laureandosi 2.0';
    }

    public function getEmailFromMail(): string
    {
        return self::$rootData['email']['from-mail'] ?? 'noreply-laureandosi@dii.unipi.it';
    }
    public function getEmailBody(): string
    {
        return self::$rootData['email']['body'] ?? 'Gentile laureando, in allegato il prospetto.';
    }

    public function getEmailSubject(string $cdl): string
    {
        $subject = self::$rootData['email']['subject'] ?? "Appello di laurea - indicatori per voto di laurea";
        return str_replace('INSERISCI_CDL', $cdl, $subject);
    }

    public function getEsamiNonCarriera(string $cdl, string $matricola = '*'): array
    {
        $filtri = [];
        if (isset(self::$filtriData[$cdl]['*']['da_togliere'])) {
            $filtri = array_merge($filtri, self::$filtriData[$cdl]['*']['da_togliere']);
        }
        if (isset(self::$filtriData[$cdl][$matricola]['da_togliere'])) {
            $filtri = array_merge($filtri, self::$filtriData[$cdl][$matricola]['da_togliere']);
        }
        return array_map('strtoupper', array_unique($filtri));
    }

    public function getEsamiNonMedia(string $cdl, string $matricola = '*'): array
    {
        $filtri = [];
        if (isset(self::$filtriData[$cdl]['*']['non_media'])) {
            $filtri = array_merge($filtri, self::$filtriData[$cdl]['*']['non_media']);
        }
        if (isset(self::$filtriData[$cdl][$matricola]['non_media'])) {
            $filtri = array_merge($filtri, self::$filtriData[$cdl][$matricola]['non_media']);
        }
        return array_map('strtoupper', array_unique($filtri));
    }

    public function getEsamiInf(): array
    {
        return self::$esamiInfData['esami_info'] ?? [];
    }
}