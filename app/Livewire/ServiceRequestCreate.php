<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class ServiceRequestCreate extends Component
{
    use WithFileUploads;

    public $categoryId = '';
    public string $title = '';
    public string $description = '';
    public $budget = '';
    public $deadline = '';
    public string $city = '';
    public string $address = '';
    public $attachments = [];

    protected $rules = [
        'categoryId' => 'required|exists:categories,id',
        'title' => 'required|string|min:5|max:150',
        'description' => 'required|string|min:20|max:2000',
        'budget' => 'nullable|numeric|min:0|max:100000000',
        'deadline' => 'nullable|integer|min:1|max:365',
        'city' => 'required|string|max:100',
        'address' => 'nullable|string|max:255',
        'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,rar,mp4,mp3,mov',
    ];

    protected $messages = [
        'categoryId.required' => 'Veuillez choisir une catégorie.',
        'title.required' => 'Veuillez donner un titre à votre demande.',
        'title.min' => 'Le titre doit faire au moins 5 caractères.',
        'description.required' => 'Veuillez décrire votre besoin.',
        'description.min' => 'La description doit faire au moins 20 caractères.',
        'city.required' => 'Veuillez indiquer votre ville.',
        'attachments.*.max' => 'Chaque fichier ne peut pas dépasser 10 MB.',
        'attachments.*.mimes' => 'Type de fichier non autorisé.',
    ];

    public function submit()
    {
        $validated = $this->validate();

        $uploadedAttachments = [];
        foreach ($this->attachments as $file) {
            $path = $file->store('service-requests', 'public');
            $uploadedAttachments[] = [
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'size' => $file->getSize(),
                'uploaded_at' => now()->toDateTimeString(),
            ];
        }

        $serviceRequest = ServiceRequest::create([
            'client_id' => Auth::id(),
            'category_id' => $validated['categoryId'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'budget' => $validated['budget'] !== '' ? $validated['budget'] : null,
            'deadline' => $validated['deadline'] !== '' ? $validated['deadline'] : null,
            'city' => $validated['city'],
            'address' => $validated['address'] !== '' ? $validated['address'] : null,
            'attachments' => !empty($uploadedAttachments) ? $uploadedAttachments : null,
            'status' => 'open',
        ]);

        return redirect()->route('service-requests.show', $serviceRequest)
            ->with('success', 'Votre demande a été publiée ! Les prestataires vont pouvoir y répondre.');
    }

    public function render()
    {
        return view('livewire.service-request-create', [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
        ])->layout('components.layouts.app');
    }
}
