<?php

namespace App\Support;

class ContactInfoDetector
{
    /**
     * Détecte un email, un numéro de téléphone ou un lien externe dans un texte.
     * Volontairement permissif (faux positifs possibles) : sert à un avertissement
     * doux, jamais à bloquer l'envoi — certains services nécessitent une adresse.
     */
    public static function detect(string $text): bool
    {
        if (trim($text) === '') {
            return false;
        }

        // Email
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text)) {
            return true;
        }

        // Lien externe
        if (preg_match('#https?://|(?<![\w.])www\.[a-zA-Z0-9-]+\.[a-zA-Z]{2,}#i', $text)) {
            return true;
        }

        // Numéro de téléphone : au moins 8 chiffres, éventuellement séparés
        // par des espaces, points ou tirets (ex: "97 12 34 56", "+229 97123456").
        if (preg_match_all('/\d[\d\s.\-]{6,}\d/', $text, $matches)) {
            foreach ($matches[0] as $match) {
                if (strlen(preg_replace('/\D/', '', $match)) >= 8) {
                    return true;
                }
            }
        }

        return false;
    }
}
