<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ServiceRequestsIndex extends Component
{
    use WithPagination;

    public bool $mine = false;
    public $categoryFilter = '';
    public string $cityFilter = '';

    public function mount()
    {
        $this->mine = request()->boolean('mine');
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingCityFilter()
    {
        $this->resetPage();
    }

    public function getServiceRequestsProperty()
    {
        $query = ServiceRequest::with(['client', 'category'])
            ->withCount('proposals');

        if ($this->mine) {
            $query->where('client_id', Auth::id())->latest();
        } else {
            $query->open()->recent();

            if ($this->categoryFilter !== '') {
                $query->where('category_id', $this->categoryFilter);
            }

            if ($this->cityFilter !== '') {
                $query->where('city', 'like', '%' . $this->cityFilter . '%');
            }
        }

        return $query->paginate(9);
    }

    public function render()
    {
        return view('livewire.service-requests-index', [
            'serviceRequests' => $this->serviceRequests,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
        ])->layout('components.layouts.app');
    }
}
