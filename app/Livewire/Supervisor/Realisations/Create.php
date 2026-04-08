<?php

namespace App\Livewire\Supervisor\Realisations;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Models\Realisation;
use App\Services\MediaService;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    use WithFileUploads;

    public $titre = '';
    public $description = '';
    public $date_realisation = '';
    public $lieu = '';
    public $categorie = 'evenement';
    public $is_published = false;
    public $fichiers = [];

    protected function rules()
    {
        return [
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'date_realisation' => 'required|date',
            'lieu' => 'nullable|string|max:255',
            'categorie' => 'required|in:evenement,projet,activite,inauguration,formation,autre',
            'fichiers.*' => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm|max:51200',
        ];
    }

    protected $messages = [
        'titre.required' => 'Le titre est obligatoire.',
        'date_realisation.required' => 'La date est obligatoire.',
        'fichiers.*.max' => 'Chaque fichier ne doit pas dépasser 50 MB.',
        'fichiers.*.mimes' => 'Formats acceptés: JPG, PNG, GIF, WebP, MP4, MOV, AVI, WebM.',
    ];

    public function removeFichier($index)
    {
        unset($this->fichiers[$index]);
        $this->fichiers = array_values($this->fichiers);
    }

    public function save()
    {
        $this->validate();

        $mediaService = new MediaService();
        $mediaList = [];

        foreach ($this->fichiers as $fichier) {
            try {
                $mediaList[] = $mediaService->processUploadedFile($fichier);
            } catch (\Exception $e) {
                session()->flash('error', 'Erreur lors du traitement: ' . $e->getMessage());
                return;
            }
        }

        Realisation::create([
            'titre' => $this->titre,
            'description' => $this->description ?: null,
            'date_realisation' => $this->date_realisation,
            'lieu' => $this->lieu ?: null,
            'categorie' => $this->categorie,
            'media' => $mediaList,
            'is_published' => $this->is_published,
            'created_by' => auth()->id(),
        ]);

        session()->flash('success', 'Réalisation créée avec succès.');
        return redirect()->route('supervisor.realisations.index');
    }

    public function render()
    {
        return view('livewire.supervisor.realisations.create', [
            'categories' => Realisation::getCategories(),
        ]);
    }
}

