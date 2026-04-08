<?php
namespace App\Livewire\Supervisor\Realisations;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Realisation;
use App\Services\MediaService;
#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;
    public $search = '';
    public $filterCategorie = '';
    public $filterPublished = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 12;
    protected $queryString = ['search', 'filterCategorie', 'filterPublished', 'dateFrom', 'dateTo'];
    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterCategorie() { $this->resetPage(); }
    public function updatingFilterPublished() { $this->resetPage(); }
    public function updatingDateFrom() { $this->resetPage(); }
    public function updatingDateTo() { $this->resetPage(); }
    public function resetFilters()
    {
        $this->reset(['search', 'filterCategorie', 'filterPublished', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }
    public function togglePublish(int $id)
    {
        $realisation = Realisation::findOrFail($id);
        $realisation->update(['is_published' => !$realisation->is_published]);
        session()->flash('success', $realisation->is_published ? 'Réalisation publiée.' : 'Réalisation dépubliée.');
    }
    public function delete(int $id)
    {
        $realisation = Realisation::findOrFail($id);
        $mediaService = new MediaService();
        $mediaService->deleteAllMedia($realisation->media ?? []);
        $realisation->forceDelete();
        session()->flash('success', 'Réalisation supprimée.');
    }
    protected function getBaseQuery()
    {
        return Realisation::with('createdBy')
            ->when($this->search, function ($q) {
                $q->where(function($q2) {
                    $q2->where('titre', 'like', '%' . $this->search . '%')
                       ->orWhere('description', 'like', '%' . $this->search . '%')
                       ->orWhere('lieu', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterCategorie, fn($q) => $q->where('categorie', $this->filterCategorie))
            ->when($this->filterPublished !== '', function($q) {
                if ($this->filterPublished === '1') $q->where('is_published', true);
                elseif ($this->filterPublished === '0') $q->where('is_published', false);
            })
            ->when($this->dateFrom, fn($q) => $q->whereDate('date_realisation', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('date_realisation', '<=', $this->dateTo))
            ->orderBy('date_realisation', 'desc');
    }
    public function render()
    {
        $realisations = $this->getBaseQuery()->paginate($this->perPage);
        $categories = Realisation::getCategories();
        $stats = [
            'total' => Realisation::count(),
            'published' => Realisation::where('is_published', true)->count(),
            'draft' => Realisation::where('is_published', false)->count(),
        ];
        return view('livewire.supervisor.realisations.index', compact('realisations', 'categories', 'stats'));
    }
}
