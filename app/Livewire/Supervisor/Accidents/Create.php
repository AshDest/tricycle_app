<?php

namespace App\Livewire\Supervisor\Accidents;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Models\Accident;
use App\Models\Moto;
use App\Models\Motard;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    use WithFileUploads;

    public $moto_id = '';
    public $motard_id = '';
    public $date_heure = '';
    public $lieu = '';
    public $description = '';
    public $temoignage_motard = '';
    public $temoignage_temoin = '';
    public $temoin_nom = '';
    public $temoin_telephone = '';
    public $gravite = 'mineur';
    public $pieces_endommagees = '';
    public $estimation_cout = 0;
    public $prise_en_charge = 'motard';
    public $statut = 'declare';
    public $notes_admin = '';

    protected function rules()
    {
        return [
            'moto_id' => 'required|exists:motos,id',
            'motard_id' => 'required|exists:motards,id',
            'date_heure' => 'required|date',
            'lieu' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'temoignage_motard' => 'nullable|string|max:1000',
            'temoignage_temoin' => 'nullable|string|max:1000',
            'temoin_nom' => 'nullable|string|max:255',
            'temoin_telephone' => 'nullable|string|max:20',
            'gravite' => 'required|in:mineur,modere,grave',
            'pieces_endommagees' => 'nullable|string|max:500',
            'estimation_cout' => 'nullable|numeric|min:0',
            'prise_en_charge' => 'required|in:motard,proprietaire,assurance,nth',
            'statut' => 'required|in:declare,evalue,reparation_programmee,repare,cloture',
            'notes_admin' => 'nullable|string|max:500',
        ];
    }

    protected $messages = [
        'moto_id.required' => 'Veuillez sélectionner une moto.',
        'motard_id.required' => 'Veuillez sélectionner le motard impliqué.',
        'date_heure.required' => 'La date et l\'heure sont obligatoires.',
        'lieu.required' => 'Le lieu de l\'accident est obligatoire.',
        'description.required' => 'La description de l\'accident est obligatoire.',
    ];

    public function updatedMotoId($value)
    {
        if ($value) {
            $moto = Moto::with('motard')->find($value);
            if ($moto && $moto->motard_id) {
                $this->motard_id = $moto->motard_id;
            }
        }
    }

    public function save()
    {
        $this->validate();

        Accident::create([
            'moto_id' => $this->moto_id,
            'motard_id' => $this->motard_id,
            'date_heure' => $this->date_heure,
            'lieu' => $this->lieu,
            'description' => $this->description,
            'temoignage_motard' => $this->temoignage_motard ?: null,
            'temoignage_temoin' => $this->temoignage_temoin ?: null,
            'temoin_nom' => $this->temoin_nom ?: null,
            'temoin_telephone' => $this->temoin_telephone ?: null,
            'gravite' => $this->gravite,
            'pieces_endommagees' => $this->pieces_endommagees ?: null,
            'estimation_cout' => $this->estimation_cout ?: 0,
            'prise_en_charge' => $this->prise_en_charge,
            'statut' => $this->statut,
            'notes_admin' => $this->notes_admin ?: null,
        ]);

        session()->flash('success', 'Accident enregistré avec succès.');
        return redirect()->route('supervisor.accidents.index');
    }

    public function render()
    {
        $motos = Moto::with(['proprietaire.user', 'motard.user'])
            ->orderBy('plaque_immatriculation')
            ->get();

        $motards = Motard::with('user')
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        return view('livewire.supervisor.accidents.create', [
            'motos' => $motos,
            'motards' => $motards,
            'niveauxGravite' => Accident::getNiveauxGravite(),
            'prisesEnCharge' => Accident::getPrisesEnCharge(),
        ]);
    }
}

