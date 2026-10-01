<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'advertiser_name',
        'advertiser_contact',
        'image',
        'link_url',
        'placement',
        'order',
        'starts_at',
        'ends_at',
        'is_active',
        'impressions',
        'clicks',
        'price_paid',
        'notes',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'is_active' => 'boolean',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'price_paid' => 'decimal:2',
    ];

    public static function placements(): array
    {
        return [
            'home_banner' => 'Bannière accueil',
            'services_sidebar' => 'Barre latérale — liste des services',
        ];
    }

    public function getPlacementLabelAttribute(): string
    {
        return self::placements()[$this->placement] ?? $this->placement;
    }

    // Scopes
    public function scopeActive($query)
    {
        $today = now()->toDateString();

        return $query->where('is_active', true)
            ->where('starts_at', '<=', $today)
            ->where('ends_at', '>=', $today);
    }

    public function scopeForPlacement($query, string $placement)
    {
        return $query->where('placement', $placement);
    }

    // Helpers
    // Dédoublonné par visiteur (ou session pour un invité) et par heure, même principe que
    // ServiceView (audit externe — 6e audit, clé invité passée à la session au 7e audit) : sans
    // ça, un rechargement de page, un robot ou un rafraîchissement automatique gonflait les
    // impressions sans aucune limite, avec une écriture en base à chaque affichage de la
    // bannière. session()->getId() plutôt que request()->ip() pour un invité : au Bénin, de
    // nombreux abonnés mobiles partagent la même IP publique (NAT opérateur), ce qui sous-comptait
    // fortement les impressions/clics invités.
    public function recordImpression(): void
    {
        $viewerKey = auth()->id() ?? 'guest:' . session()->getId();
        $throttleKey = "ad-impression:{$this->id}:{$viewerKey}";

        if (Cache::has($throttleKey)) {
            return;
        }

        Cache::put($throttleKey, true, now()->addHour());

        $this->increment('impressions');
    }

    // Même dédoublonnage que recordImpression() (audit externe — 6e audit). Le clic redirige
    // toujours vers le lien de l'annonceur (AdvertisementController::click()), que ce clic précis
    // soit compté ou non.
    public function recordClick(): void
    {
        $viewerKey = auth()->id() ?? 'guest:' . session()->getId();
        $throttleKey = "ad-click:{$this->id}:{$viewerKey}";

        if (Cache::has($throttleKey)) {
            return;
        }

        Cache::put($throttleKey, true, now()->addHour());

        $this->increment('clicks');
    }
}
