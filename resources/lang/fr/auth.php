<?php

// Sans ce fichier, Laravel affichait littéralement les clés brutes "auth.failed" à l'écran
// (repéré en QA - BUG003) au lieu d'un message humain : resources/lang/fr.json ne couvre que
// les chaînes passées à __() mot pour mot, pas les groupes de traduction internes de Laravel
// (auth.*, passwords.*, validation.*) qui n'avaient jamais été publiés/traduits en français.
return [

    'failed' => 'Ces identifiants ne correspondent pas à nos enregistrements.',
    'password' => 'Le mot de passe fourni est incorrect.',
    'throttle' => 'Trop de tentatives de connexion. Veuillez réessayer dans :seconds secondes.',

];
