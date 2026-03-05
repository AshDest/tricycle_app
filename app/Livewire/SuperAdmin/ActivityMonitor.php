<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class ActivityMonitor extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filterRole = '';
    public $filterStatus = '';
    public $onlineOnly = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterRole' => ['except' => ''],
        'filterStatus' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Récupérer les utilisateurs connectés récemment (dernières 24h ou en session active)
        $users = User::with('roles')
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterRole, function ($query) {
                $query->whereHas('roles', function($q) {
                    $q->where('name', $this->filterRole);
                });
            })
            ->when($this->filterStatus === 'active', function ($query) {
                $query->where('is_active', true);
            })
            ->when($this->filterStatus === 'inactive', function ($query) {
                $query->where('is_active', false);
            })
            ->when($this->onlineOnly, function ($query) {
                // Utilisateurs actifs dans les dernières 15 minutes
                $query->where('last_activity', '>=', now()->subMinutes(15));
            })
            ->orderBy('last_activity', 'desc')
            ->paginate(20);

        // Statistiques
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'online_now' => User::where('last_activity', '>=', now()->subMinutes(15))->count(),
            'active_today' => User::whereDate('last_activity', today())->count(),
        ];

        // Répartition par rôle
        $roleStats = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name', DB::raw('count(*) as count'))
            ->groupBy('roles.name')
            ->get()
            ->pluck('count', 'name')
            ->toArray();

        // Rôles disponibles pour le filtre
        $roles = DB::table('roles')->pluck('name')->toArray();

        return view('livewire.super-admin.activity-monitor', [
            'users' => $users,
            'stats' => $stats,
            'roleStats' => $roleStats,
            'roles' => $roles,
        ]);
    }

    public function toggleUserStatus($userId)
    {
        $user = User::find($userId);

        if ($user && $user->id !== auth()->id()) {
            $user->is_active = !$user->is_active;
            $user->save();

            session()->flash('success', "Statut de l'utilisateur mis à jour.");
        } else {
            session()->flash('error', "Impossible de modifier votre propre statut.");
        }
    }

    public function forceLogout($userId)
    {
        $user = User::find($userId);

        if ($user && $user->id !== auth()->id()) {
            // Supprimer les sessions de l'utilisateur
            DB::table('sessions')
                ->where('user_id', $userId)
                ->delete();

            $user->last_activity = null;
            $user->save();

            session()->flash('success', "L'utilisateur a été déconnecté.");
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterRole', 'filterStatus', 'onlineOnly']);
        $this->resetPage();
    }
}

