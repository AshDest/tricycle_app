<?php

namespace App\Livewire\Admin\Cleaners;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Cleaner;
use App\Models\Lavage;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Show extends Component
{
    public Cleaner $cleaner;

    public function mount(Cleaner $cleaner)
    {
        $this->cleaner = $cleaner->load('user');
    }

    public function render()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Statistiques
        $stats = [
            'lavages_jour' => Lavage::where('cleaner_id', $this->cleaner->id)
                ->whereDate('date_lavage', $today)->count(),
            'lavages_mois' => Lavage::where('cleaner_id', $this->cleaner->id)
                ->whereBetween('date_lavage', [$startOfMonth, now()])->count(),
            'ca_jour' => Lavage::where('cleaner_id', $this->cleaner->id)
                ->whereDate('date_lavage', $today)
                ->where('statut_paiement', 'payé')
                ->sum('part_cleaner'),
            'ca_mois' => Lavage::where('cleaner_id', $this->cleaner->id)
                ->whereBetween('date_lavage', [$startOfMonth, now()])
                ->where('statut_paiement', 'payé')
                ->sum('part_cleaner'),
            'part_okami_mois' => Lavage::where('cleaner_id', $this->cleaner->id)
                ->whereBetween('date_lavage', [$startOfMonth, now()])
                ->where('statut_paiement', 'payé')
                ->sum('part_okami'),
        ];

        // Derniers lavages
        $derniersLavages = Lavage::where('cleaner_id', $this->cleaner->id)
            ->with('moto')
            ->orderBy('date_lavage', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.admin.cleaners.show', [
            'stats' => $stats,
            'derniersLavages' => $derniersLavages,
        ]);
    }
}

