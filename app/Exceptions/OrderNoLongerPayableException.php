<?php

namespace App\Exceptions;

// Levée par PaymentService::initiateForOrder() quand la commande n'est plus 'pending_payment' au
// moment de démarrer le paiement (annulée ou expirée entre le contrôle de l'appelant, fait sans
// verrou, et la prise du verrou dans initiateForOrder()) : une condition métier attendue, pas une
// erreur technique. Distinguée de \RuntimeException générique (audit externe — 5e audit) pour que
// les appelants affichent un message adapté ("réessayez" ne mène à rien ici) et ne la fassent pas
// remonter dans les logs via report() comme une vraie panne.
class OrderNoLongerPayableException extends \RuntimeException
{
}
