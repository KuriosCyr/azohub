<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PrestataireOrdersIndex extends Component
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

        $query = Order::where('prestataire_id', Auth::id())
            ->with(['client', 'service', 'serviceRequest', 'customOffer']);

        match ($this->status) {
            'active' => $query->whereIn('status', ['paid', 'in_progress']),
            'delivered' => $query->where('status', 'delivered'),
            'completed' => $query->where('status', 'completed'),
            'cancelled' => $query->where('status', 'cancelled'),
            'disputed' => $query->where('status', 'disputed'),
            default => null,
        };

        if ($this->search !== '') {
            $like = '%' . addcslashes($this->search, '%_\\') . '%';
            $query->where(function ($q) use ($like) {
                $q->where('order_number', 'like', $like)
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', $like));
            });
        }

        $orders = $query->latest()->paginate(15);

        $counts = [
            'all' => Order::where('prestataire_id', Auth::id())->count(),
            'active' => Order::where('prestataire_id', Auth::id())->whereIn('status', ['paid', 'in_progress'])->count(),
            'delivered' => Order::where('prestataire_id', Auth::id())->where('status', 'delivered')->count(),
            'completed' => Order::where('prestataire_id', Auth::id())->where('status', 'completed')->count(),
            'cancelled' => Order::where('prestataire_id', Auth::id())->where('status', 'cancelled')->count(),
            'disputed' => Order::where('prestataire_id', Auth::id())->where('status', 'disputed')->count(),
        ];

        return view('livewire.prestataire-orders-index', [
            'orders' => $orders,
            'counts' => $counts,
        ])->layout('components.layouts.app');
    }
}
