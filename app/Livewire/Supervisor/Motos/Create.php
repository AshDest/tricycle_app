<?php

namespace App\Livewire\Supervisor\Motos;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Moto;
use App\Models\Proprietaire;
use App\Models\Motard;
use Illuminate\Support\Facades\DB;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public string $plaque_immatriculation = '';
    public string $numero_chassis = '';
    public string $marque = '';
    public string $modele = '';
    public string $annee = '';
    public string $couleur = '';
    public string $proprietaire_id = '';
    public string $motard_id = '';
    public string $statut = 'actif';

    protected function rules(): array
    {
        return [
            'plaque_immatriculation' => 'required|string|unique:motos,plaque_immatriculation',
            'numero_chassis' => 'nullable|string|unique:motos,numero_chassis',
            'marque' => 'nullable|string|max:100',
            'modele' => 'nullable|string|max:100',
            'annee' => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'couleur' => 'nullable|string|max:50',
            'proprietaire_id' => 'required|exists:proprietaires,id',
            'motard_id' => 'nullable|exists:motards,id',
            'statut' => 'required|in:actif,inactif,en_maintenance',
        ];
    }

    protected $messages = [
        'plaque_immatriculation.required' => 'La plaque d\'immatriculation est obligatoire.',
        'plaque_immatriculation.unique' => 'Cette plaque existe déjà.',
        'numero_chassis.unique' => 'Ce numéro de châssis existe déjà.',
        'proprietaire_id.required' => 'Le propriétaire est obligatoire.',
    ];

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $moto = Moto::create([
                'plaque_immatriculation' => $this->plaque_immatriculation,
                'numero_chassis' => $this->numero_chassis ?: null,
                'marque' => $this->marque ?: null,
                'modele' => $this->modele ?: null,
                'annee_fabrication' => $this->annee ?: null,
                'couleur' => $this->couleur ?: null,
                'proprietaire_id' => $this->proprietaire_id,
                'statut' => $this->statut,
            ]);

            // Assigner le motard si spécifié
            if ($this->motard_id) {
                $motard = Motard::find($this->motard_id);
                if ($motard) {
                    $motard->update(['moto_id' => $moto->id]);
                }
            }

            DB::commit();

            session()->flash('success', 'Moto créée avec succès.');
            return redirect()->route('supervisor.motos.index');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $proprietaires = Proprietaire::with('user')->get();
        $motards = Motard::with('user')
            ->whereDoesntHave('motoActuelle')
            ->where('is_active', true)
            ->get();

        return view('livewire.supervisor.motos.create', compact('proprietaires', 'motards'));
    }
}

