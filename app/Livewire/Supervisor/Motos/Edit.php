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

    public function mount(Moto $moto): void
    {
        $this->moto = $moto->load(['proprietaire', 'motardActuel']);

        $this->plaque_immatriculation = $moto->plaque_immatriculation ?? '';
        $this->numero_chassis = $moto->numero_chassis ?? '';
        $this->marque = $moto->marque ?? '';
        $this->modele = $moto->modele ?? '';
        $this->annee = $moto->annee_fabrication ?? '';
        $this->couleur = $moto->couleur ?? '';
        $this->proprietaire_id = (string) $moto->proprietaire_id;
        $this->motard_id = (string) ($moto->motardActuel?->id ?? '');
        $this->statut = $moto->statut ?? 'actif';
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
                'statut' => $this->statut,
            ]);

            // Gérer l'affectation du motard
            $ancienMotard = Motard::where('moto_id', $this->moto->id)->first();

            if ($ancienMotard && $ancienMotard->id != $this->motard_id) {
                $ancienMotard->update(['moto_id' => null]);
            }

            if ($this->motard_id) {
                $nouveauMotard = Motard::find($this->motard_id);
                if ($nouveauMotard) {
                    $nouveauMotard->update(['moto_id' => $this->moto->id]);
                }
            }

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
        $motards = Motard::with('user')
            ->where(function ($q) {
                $q->whereDoesntHave('motoActuelle')
                  ->orWhere('moto_id', $this->moto->id);
            })
            ->where('is_active', true)
            ->get();

        return view('livewire.supervisor.motos.edit', compact('proprietaires', 'motards'));
    }
}

