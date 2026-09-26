<?php

// Voir auth.php : sans ce fichier, la page "mot de passe oublié" affichait littéralement
// "passwords.sent" à l'écran (BUG002) au lieu d'un message de confirmation.
return [

    'reset' => 'Votre mot de passe a été réinitialisé.',
    'sent' => 'Nous vous avons envoyé un e-mail contenant le lien de réinitialisation de votre mot de passe.',
    'throttled' => 'Veuillez patienter avant de réessayer.',
    'token' => 'Ce jeton de réinitialisation de mot de passe est invalide.',
    'user' => "Nous ne trouvons aucun utilisateur associé à cette adresse e-mail.",

];
