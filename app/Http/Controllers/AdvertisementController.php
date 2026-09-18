<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;

class AdvertisementController extends Controller
{
    /**
     * Enregistre le clic puis redirige vers le lien de l'annonceur.
     */
    public function click(Advertisement $advertisement)
    {
        $advertisement->recordClick();

        return redirect()->away($advertisement->link_url);
    }
}
