<?php

namespace App\Livewire\Admin\Recompenses;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Recompense;
use App\Models\Motard;
use App\Services\PerformanceService;
use Carbon\Carbon;

/**
 * Créer une nouvelle récompense manuelle
 */
#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public $motard_id = '';
    public $type = 'badge_or';
    public $categorie = 'excellence';
    public $titre = '';
    public $description = '';
    public $montant_prime = '';
    public $periode_debut;
    public $periode_fin;
    public $notes = '';

    // Pour charger les scores automatiquement
    public $motardSelectionne = null;
    public $performanceMotard = null;

    protected function rules()
    {
        return [
            'motard_id' => 'required|exists:motards,id',
            'type' => 'required|in:' . implode(',', array_keys(Recompense::getTypes())),
            'categorie' => 'required|in:' . implode(',', array_keys(Recompense::getCategories())),
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'montant_prime' => 'nullable|numeric|min:0',
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after_or_equal:periode_debut',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function mount()
    {
        $this->periode_debut = now()->startOfMonth()->format('Y-m-d');
        $this->periode_fin = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedMotardId($value)
    {
        if ($value) {
            $this->motardSelectionne = Motard::with('user')->find($value);

            // Charger les performances du mois
            $service = new PerformanceService();
            $mois = Carbon::parse($this->periode_debut)->month;
            $annee = Carbon::parse($this->periode_debut)->year;

            $this->performanceMotard = $service->calculerPerformanceMensuelle(
                $this->motardSelectionne, $mois, $annee
            );

            // Pré-remplir le titre
            if (!$this->titre) {
                $this->titre = "Récompense pour " . $this->motardSelectionne->user->name;
            }
        } else {
            $this->motardSelectionne = null;
            $this->performanceMotard = null;
        }
    }

    public function updatedType($value)
    {
        // Mettre à jour le titre automatiquement
        if ($this->motardSelectionne) {
            $types = Recompense::getTypes();
            $this->titre = ($types[$value]['label'] ?? $value) . " - " . $this->motardSelectionne->user->name;
        }
    }

    public function submit()
    {
        $this->validate();

        $scores = [
            'score_regularite' => $this->performanceMotard->score_regularite ?? 0,
            'score_securite' => $this->performanceMotard->score_securite ?? 0,
            'score_versement' => $this->performanceMotard->score_versement ?? 0,
            'score_total' => $this->performanceMotard->score_total ?? 0,
        ];

        Recompense::create([
            'motard_id' => $this->motard_id,
            'type' => $this->type,
            'categorie' => $this->categorie,
            'titre' => $this->titre,
            'description' => $this->description,
            'montant_prime' => $this->montant_prime ?: null,
            'periode_debut' => $this->periode_debut,
            'periode_fin' => $this->periode_fin,
            'notes' => $this->notes,
            'statut' => 'attribue',
            ...$scores,
        ]);

        session()->flash('success', 'Récompense créée avec succès.');
        return redirect()->route('admin.recompenses.index');
    }

    public function render()
    {
        $motards = Motard::with('user')
            ->where('is_active', true)
            ->get()
            ->sortBy(fn($m) => $m->user->name ?? '');

        return view('livewire.admin.recompenses.create', [
            'motards' => $motards,
            'types' => Recompense::getTypes(),
            'categories' => Recompense::getCategories(),
        ]);
    }
}

