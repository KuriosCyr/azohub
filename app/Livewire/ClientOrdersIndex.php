<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

// Page dédiée "Mes commandes" côté client — avant, la liste complète (déjà paginée)
// vivait à l'intérieur du dashboard, et le lien "Mes commandes" du menu pointait en
// fait vers le dashboard lui-même, pas vers une vraie page séparée.
class ClientOrdersIndex extends Component
{
    use WithPagination;

    public string $status = 'all';
    public string $search = '';

    protected $queryString = [
        'status' => ['except' => 'all'],
        'search' => ['except' => ''],
    ];

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->search = mb_substr((string) $this->search, 0, 100);
        $userId = Auth::id();

        $query = Order::where('client_id', $userId)
            ->with(['prestataire', 'service.category', 'serviceRequest.category', 'customOffer']);

        match ($this->status) {
            'active' => $query->whereIn('status', ['pending_payment', 'paid', 'in_progress']),
            'awaiting' => $query->where('status', 'delivered'),
            'completed' => $query->where('status', 'completed'),
            'cancelled' => $query->where('status', 'cancelled'),
            default => null,
        };

        if ($this->search !== '') {
            $like = '%' . addcslashes($this->search, '%_\\') . '%';
            $query->where(function ($q) use ($like) {
                $q->where('order_number', 'like', $like)
                    ->orWhereHas('service', fn ($s) => $s->where('title', 'like', $like))
                    ->orWhereHas('serviceRequest', fn ($s) => $s->where('title', 'like', $like))
                    ->orWhereHas('customOffer', fn ($s) => $s->where('title', 'like', $like))
                    ->orWhereHas('prestataire', fn ($p) => $p->where('name', 'like', $like));
            });
        }

        $orders = $query->latest()->paginate(10);

        $counts = [
            'all' => Order::where('client_id', $userId)->count(),
            'active' => Order::where('client_id', $userId)->whereIn('status', ['pending_payment', 'paid', 'in_progress'])->count(),
            'awaiting' => Order::where('client_id', $userId)->where('status', 'delivered')->count(),
            'completed' => Order::where('client_id', $userId)->where('status', 'completed')->count(),
            'cancelled' => Order::where('client_id', $userId)->where('status', 'cancelled')->count(),
        ];

        return view('livewire.client-orders-index', [
            'orders' => $orders,
            'counts' => $counts,
        ])->layout('components.layouts.app');
    }
}
