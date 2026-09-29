<?php

namespace Tests\Unit;

use Illuminate\Contracts\Queue\ShouldQueue;
use Tests\TestCase;

// Garde-fou permanent (suite à un audit externe) : les 29 notifications de l'app étaient
// toutes envoyées de façon synchrone (aucune en ShouldQueue), y compris à l'intérieur de la
// transaction du webhook FedaPay — un SMTP lent ou en panne pouvait ralentir ou faire échouer
// un paiement. Ce test empêche qu'une future notification oublie ShouldQueue par mégarde.
class NotificationsAreQueuedTest extends TestCase
{
    public function test_every_notification_class_implements_should_queue(): void
    {
        $files = glob(app_path('Notifications/*.php'));
        $this->assertNotEmpty($files, 'Aucune classe de notification trouvée — le chemin a-t-il changé ?');

        $missing = [];

        foreach ($files as $file) {
            $class = 'App\\Notifications\\' . basename($file, '.php');

            if (!is_subclass_of($class, \Illuminate\Notifications\Notification::class)) {
                continue;
            }

            if (!is_subclass_of($class, ShouldQueue::class)) {
                $missing[] = $class;
            }
        }

        $this->assertEmpty($missing, 'Ces notifications ne sont pas en file (ShouldQueue manquant) : ' . implode(', ', $missing));
    }
}
