<?php

class GestoreFileSystem
{
    // Metodo per calcolare il percorso
    public static function getPercorsoCartella(string $cdl): string
    {
        // Pulisce il nome del CdL per evitare problemi nel File System (es. toglie gli spazi)
        $nomeCartellaSicuro = preg_replace('/[^a-zA-Z0-9]/', '_', $cdl);
        return __DIR__ . '/../Prospetti_Correnti/' . $nomeCartellaSicuro . '/';
    }

    // Pulisce o crea la cartella
    public static function preparaCartella(string $cdl): void
    {
        $pathCartella = self::getPercorsoCartella($cdl);

        // Se non esiste, la creiamo
        if (!is_dir($pathCartella)) {
            if (!mkdir($pathCartella, 0777, true)) {
                throw new Exception("Impossibile creare la cartella: $pathCartella");
            }
        } else {
            // Se esiste, la svuotiamo
            $files = glob($pathCartella . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file); // Elimina il file
                }
            }
        }
    }
}