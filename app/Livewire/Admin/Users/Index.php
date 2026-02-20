<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterRole = '';
    public $perPage = 15;

    protected $queryString = ['search', 'filterRole'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterRole()
    {
        $this->resetPage();
    }

    public function delete(User $user)
    {
        $user->delete();
        session()->flash('success', 'Utilisateur supprime avec succes.');
    }

    protected function getBaseQuery()
    {
        return User::when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterRole, function ($q) {
                $q->role($this->filterRole);
            })
            ->orderBy('created_at', 'desc');
    }

    public function exportPdf()
    {
        $users = $this->getBaseQuery()->get();

        $stats = [
            'total' => $users->count(),
            'admins' => $users->filter(fn($u) => $u->hasRole('admin'))->count(),
            'supervisors' => $users->filter(fn($u) => $u->hasRole('supervisor'))->count(),
            'actifs' => $users->where('is_active', true)->count(),
        ];

        $pdf = Pdf::loadView('pdf.lists.users', [
            'users' => $users,
            'stats' => $stats,
            'title' => 'Liste des Utilisateurs',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
            'filtres' => [
                'search' => $this->search,
                'role' => $this->filterRole,
            ],
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'utilisateurs_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        $users = $this->getBaseQuery()->paginate($this->perPage);
        $roles = \Spatie\Permission\Models\Role::all();

        return view('livewire.admin.users.index', compact('users', 'roles'));
    }
}
