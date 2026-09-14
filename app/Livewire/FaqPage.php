<?php

namespace App\Livewire;

use App\Models\Faq;
use Livewire\Component;

class FaqPage extends Component
{
    public $search = '';
    public $selectedCategory = 'all';
    public $openFaqId = null;

    public function mount()
    {
        // Ouvrir la première FAQ par défaut
        $firstFaq = Faq::active()->ordered()->first();
        if ($firstFaq) {
            $this->openFaqId = $firstFaq->id;
        }
    }

    public function toggleFaq($faqId)
    {
        if ($this->openFaqId === $faqId) {
            $this->openFaqId = null;
        } else {
            $this->openFaqId = $faqId;
        }
    }

    public function setCategory($category)
    {
        $this->selectedCategory = $category;
        $this->search = '';
        $this->openFaqId = null;
    }

    public function getFaqsProperty()
    {
        $query = Faq::active()->ordered();

        // Filtre par catégorie
        if ($this->selectedCategory !== 'all') {
            $query->byCategory($this->selectedCategory);
        }

        // Recherche
        if ($this->search) {
            $query->where(function($q) {
                $q->where('question', 'like', '%' . $this->search . '%')
                  ->orWhere('answer', 'like', '%' . $this->search . '%');
            });
        }

        return $query->get();
    }

    public function getFaqsByCategoryProperty()
    {
        $categories = Faq::categories();
        $grouped = [];

        foreach ($categories as $key => $label) {
            $faqs = Faq::active()
                ->byCategory($key)
                ->ordered()
                ->get();
            
            if ($faqs->count() > 0) {
                $grouped[$key] = [
                    'label' => $label,
                    'faqs' => $faqs,
                ];
            }
        }

        return $grouped;
    }

    public function render()
    {
        return view('livewire.faq-page', [
            'faqs' => $this->faqs,
            'faqsByCategory' => $this->faqsByCategory,
            'categories' => Faq::categories(),
        ])->layout('components.layouts.public');  // ← AJOUTÉ ICI !
    }
}