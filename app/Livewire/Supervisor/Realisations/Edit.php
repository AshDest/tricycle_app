<?php

namespace App\Livewire\Supervisor\Realisations;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Models\Realisation;
use App\Services\MediaService;

#[Layout('components.dashlite-layout')]
class Edit extends Component
{
    use WithFileUploads;

    public Realisation $realisation;

    public $titre = '';
    public $description = '';
    public $date_realisation = '';
    public $lieu = '';
    public $categorie = '';
    public $is_published = false;
    public $nouveaux_fichiers = [];

    public function mount(Realisation $realisation)
    {
        $this->realisation = $realisation;
        $this->titre = $realisation->titre;
        $this->description = $realisation->description ?? '';
        $this->date_realisation = $realisation->date_realisation?->format('Y-m-d') ?? '';
        $this->lieu = $realisation->lieu ?? '';
        $this->categorie = $realisation->categorie;
        $this->is_published = $realisation->is_published;
    }

    protected function rules()
    {
        return [
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'date_realisation' => 'required|date',
            'lieu' => 'nullable|string|max:255',
            'categorie' => 'required|in:evenement,projet,activite,inauguration,formation,autre',
            'nouveaux_fichiers.*' => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm|max:51200',
        ];
    }

    protected $messages = [
        'titre.required' => 'Le titre est obligatoire.',
        'date_realisation.required' => 'La date est obligatoire.',
        'nouveaux_fichiers.*.max' => 'Chaque fichier ne doit pas dépasser 50 MB.',
        'nouveaux_fichiers.*.mimes' => 'Formats acceptés: JPG, PNG, GIF, WebP, MP4, MOV, AVI, WebM.',
    ];

    public function removeNewFile($index)
    {
        unset($this->nouveaux_fichiers[$index]);
        $this->nouveaux_fichiers = array_values($this->nouveaux_fichiers);
    }

    public function deleteMedia(int $index)
    {
        $media = $this->realisation->media ?? [];
        if (isset($media[$index])) {
            $mediaService = new MediaService();
            $mediaService->deleteMedia($media[$index]);
            unset($media[$index]);
            $this->realisation->update(['media' => array_values($media)]);
            $this->realisation->refresh();
            session()->flash('success', 'Média supprimé.');
        }
    }

    public function save()
    {
        $this->validate();

        $mediaService = new MediaService();
        $mediaList = $this->realisation->media ?? [];

        foreach ($this->nouveaux_fichiers as $fichier) {
            try {
                $mediaList[] = $mediaService->processUploadedFile($fichier);
            } catch (\Exception $e) {
                session()->flash('error', 'Erreur lors du traitement: ' . $e->getMessage());
                return;
            }
        }

        $this->realisation->update([
            'titre' => $this->titre,
            'description' => $this->description ?: null,
            'date_realisation' => $this->date_realisation,
            'lieu' => $this->lieu ?: null,
            'categorie' => $this->categorie,
            'media' => $mediaList,
            'is_published' => $this->is_published,
        ]);

        session()->flash('success', 'Réalisation mise à jour avec succès.');
        return redirect()->route('supervisor.realisations.show', $this->realisation);
    }

    public function render()
    {
        return view('livewire.supervisor.realisations.edit', [
            'categories' => Realisation::getCategories(),
        ]);
    }
}

