<?php

namespace App\Livewire\Supervisor\Motos;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Moto;
use App\Models\Proprietaire;
use App\Models\Motard;
use Illuminate\Support\Facades\DB;

#[Layout('components.dashlite-layout')]
class Edit extends Component
{
    public Moto $moto;

    public string $plaque_immatriculation = '';
    public string $numero_chassis = '';
    public string $marque = '';
    public string $modele = '';
    public string $annee = '';
    public string $couleur = '';
    public string $proprietaire_id = '';
    public string $motard_id = '';
    public string $statut = 'actif';

    // Champs de contrat
    public string $contrat_debut = '';
    public string $contrat_fin = '';
    public string $contrat_numero = '';
    public string $contrat_notes = '';

    public function mount(Moto $moto): void
    {
        $this->moto = $moto->load(['proprietaire', 'motard']);

        $this->plaque_immatriculation = $moto->plaque_immatriculation ?? '';
        $this->numero_chassis = $moto->numero_chassis ?? '';
        $this->marque = $moto->marque ?? '';
        $this->modele = $moto->modele ?? '';
        $this->annee = $moto->annee_fabrication ?? '';
        $this->couleur = $moto->couleur ?? '';
        $this->proprietaire_id = (string) $moto->proprietaire_id;
        $this->motard_id = (string) ($moto->motard_id ?? '');
        $this->statut = $moto->statut ?? 'actif';

        // Champs de contrat
        $this->contrat_debut = $moto->contrat_debut?->format('Y-m-d') ?? '';
        $this->contrat_fin = $moto->contrat_fin?->format('Y-m-d') ?? '';
        $this->contrat_numero = $moto->contrat_numero ?? '';
        $this->contrat_notes = $moto->contrat_notes ?? '';
    }

    protected function rules(): array
    {
        return [
            'plaque_immatriculation' => 'required|string|unique:motos,plaque_immatriculation,' . $this->moto->id,
            'numero_chassis' => 'nullable|string|unique:motos,numero_chassis,' . $this->moto->id,
            'marque' => 'nullable|string|max:100',
            'modele' => 'nullable|string|max:100',
            'annee' => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'couleur' => 'nullable|string|max:50',
            'proprietaire_id' => 'required|exists:proprietaires,id',
            'motard_id' => 'nullable|exists:motards,id',
            'statut' => 'required|in:actif,inactif,en_maintenance',
            'contrat_debut' => 'nullable|date',
            'contrat_fin' => 'nullable|date|after_or_equal:contrat_debut',
            'contrat_numero' => 'nullable|string|max:100',
            'contrat_notes' => 'nullable|string|max:500',
        ];
    }

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $this->moto->update([
                'plaque_immatriculation' => $this->plaque_immatriculation,
                'numero_chassis' => $this->numero_chassis ?: null,
                'marque' => $this->marque ?: null,
                'modele' => $this->modele ?: null,
                'annee_fabrication' => $this->annee ?: null,
                'couleur' => $this->couleur ?: null,
                'proprietaire_id' => $this->proprietaire_id,
                'motard_id' => $this->motard_id ?: null,
                'statut' => $this->statut,
                'contrat_debut' => $this->contrat_debut ?: null,
                'contrat_fin' => $this->contrat_fin ?: null,
                'contrat_numero' => $this->contrat_numero ?: null,
                'contrat_notes' => $this->contrat_notes ?: null,
            ]);


            DB::commit();

            session()->flash('success', 'Moto mise à jour avec succès.');
            return redirect()->route('supervisor.motos.index');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $proprietaires = Proprietaire::with('user')->get();

        // Récupérer les motards disponibles (sans moto assignée) ou celui déjà assigné à cette moto
        $motards = Motard::with('user')
            ->where('is_active', true)
            ->where(function ($q) {
                // Motards sans moto assignée
                $q->whereDoesntHave('moto')
                  // Ou le motard actuellement assigné à cette moto
                  ->orWhereHas('moto', function($q2) {
                      $q2->where('motos.id', $this->moto->id);
                  });
            })
            ->get();

        return view('livewire.supervisor.motos.edit', compact('proprietaires', 'motards'));
    }
}

