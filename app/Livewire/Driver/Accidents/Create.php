<?php

namespace App\Livewire\Driver\Accidents;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Accident;
use App\Models\Motard;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public $moto_id = '';
    public $date_heure = '';
    public $lieu = '';
    public $description = '';
    public $temoignage_motard = '';
    public $temoin_nom = '';
    public $temoin_telephone = '';
    public $estimation_cout = '';
    public $gravite = 'mineur';

    public $moto = null;

    protected $rules = [
        'moto_id' => 'required|exists:motos,id',
        'date_heure' => 'required',
        'lieu' => 'required|string|max:255',
        'description' => 'required|string',
        'temoignage_motard' => 'required|string',
        'temoin_nom' => 'nullable|string|max:255',
        'temoin_telephone' => 'nullable|string|max:20',
        'estimation_cout' => 'nullable|numeric|min:0',
        'gravite' => 'required|in:mineur,modere,grave',
    ];

    protected $messages = [
        'moto_id.required' => 'La moto est obligatoire.',
        'date_heure.required' => 'La date et l\'heure sont obligatoires.',
        'lieu.required' => 'Le lieu de l\'accident est obligatoire.',
        'description.required' => 'La description est obligatoire.',
        'temoignage_motard.required' => 'Votre témoignage est obligatoire.',
        'gravite.required' => 'La gravité est obligatoire.',
    ];

    public function mount()
    {
        $motard = auth()->user()->motard;
        if ($motard) {
            $this->moto = $motard->moto;
            if ($this->moto) {
                $this->moto_id = $this->moto->id;
            }
        }
        // Date par défaut : maintenant
        $this->date_heure = Carbon::now()->format('Y-m-d\TH:i');
    }

    public function save()
    {
        $this->validate();

        $motard = auth()->user()->motard;

        Accident::create([
            'moto_id' => $this->moto_id,
            'motard_id' => $motard->id,
            'date_heure' => Carbon::parse($this->date_heure),
            'lieu' => $this->lieu,
            'description' => $this->description,
            'temoignage_motard' => $this->temoignage_motard,
            'temoin_nom' => $this->temoin_nom,
            'temoin_telephone' => $this->temoin_telephone,
            'estimation_cout' => $this->estimation_cout ?? 0,
            'gravite' => $this->gravite,
            'statut' => 'declare',
        ]);

        session()->flash('success', 'Accident déclaré avec succès. L\'administration sera notifiée.');
        return redirect()->route('driver.statut');
    }

    public function render()
    {
        return view('livewire.driver.accidents.create');
    }
}
