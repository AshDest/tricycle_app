<!-- header @s -->
<div class="app-header">
    <!-- Mobile Toggle -->
    <button class="btn btn-light border-0 d-lg-none me-2" onclick="toggleSidebar()" style="padding: 0.5rem 0.75rem;">
        <i class="bi bi-list fs-4"></i>
    </button>

    <!-- Page Title (mobile) -->
    <div class="d-lg-none fw-semibold text-dark">
        @yield('title', 'Dashboard')
    </div>

    <!-- Search -->
    <div class="d-none d-lg-block header-search">
        <i class="bi bi-search search-icon"></i>
        <input type="text" class="form-control" placeholder="Rechercher motards, motos, versements...">
    </div>

    <!-- Spacer -->
    <div class="flex-grow-1"></div>

    <!-- Header Tools -->
    <div class="d-flex align-items-center gap-3">
        {{-- Quick Actions (Desktop) --}}
        <div class="dropdown d-none d-lg-block">
            <button class="btn btn-primary btn-sm d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-plus-lg"></i>
                <span>Nouveau</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow mt-2" style="min-width: 200px;">
                @role('admin')
                <li><a class="dropdown-item py-2" href="{{ route('admin.motards.create') }}"><i class="bi bi-person-plus text-primary me-2"></i>Nouveau motard</a></li>
                <li><a class="dropdown-item py-2" href="{{ route('admin.motos.create') }}"><i class="bi bi-bicycle text-info me-2"></i>Nouvelle moto</a></li>
                <li><a class="dropdown-item py-2" href="{{ route('admin.proprietaires.create') }}"><i class="bi bi-building text-warning me-2"></i>Nouveau propriétaire</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2" href="{{ route('admin.tournees.create') }}"><i class="bi bi-calendar-event text-success me-2"></i>Nouvelle tournée</a></li>
                @endrole
                @role('supervisor')
                <li><a class="dropdown-item py-2" href="{{ route('supervisor.motards.create') }}"><i class="bi bi-person-plus text-primary me-2"></i>Nouveau motard</a></li>
                <li><a class="dropdown-item py-2" href="{{ route('supervisor.motos.create') }}"><i class="bi bi-bicycle text-info me-2"></i>Nouvelle moto</a></li>
                <li><a class="dropdown-item py-2" href="{{ route('supervisor.proprietaires.create') }}"><i class="bi bi-building text-warning me-2"></i>Nouveau propriétaire</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2" href="{{ route('supervisor.maintenances.create') }}"><i class="bi bi-tools text-success me-2"></i>Nouvelle maintenance</a></li>
                <li><a class="dropdown-item py-2" href="{{ route('supervisor.accidents.create') }}"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Nouvel accident</a></li>
                @endrole
            </ul>
        </div>

        {{-- Notifications --}}
        <div class="dropdown">
            <button class="btn btn-light border-0 position-relative" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="padding: 0.5rem 0.75rem;">
                <i class="bi bi-bell fs-5"></i>
                @if(auth()->user()->unreadNotifications->count() > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-pulse" style="font-size: 0.65rem; margin-left: -8px; margin-top: 4px;">
                    {{ auth()->user()->unreadNotifications->count() > 9 ? '9+' : auth()->user()->unreadNotifications->count() }}
                </span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow p-0 mt-2" style="width: 360px; border-radius: 0.75rem;">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light" style="border-radius: 0.75rem 0.75rem 0 0;">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-bell me-2 text-primary"></i>Notifications
                    </h6>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                    <a href="#" class="text-decoration-none small text-primary fw-medium">Tout marquer lu</a>
                    @endif
                </div>
                <div style="max-height: 350px; overflow-y: auto;">
                    @forelse(auth()->user()->unreadNotifications->take(5) ?? [] as $notification)
                    <div class="px-3 py-3 border-bottom" style="transition: background 0.2s;">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="rounded-circle bg-{{ $notification->data['color'] ?? 'primary' }} bg-opacity-10 p-2 flex-shrink-0" style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-{{ $notification->data['icon'] ?? 'bell' }} text-{{ $notification->data['color'] ?? 'primary' }} fs-5"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <p class="mb-1 small text-dark fw-medium">{{ $notification->data['message'] ?? '' }}</p>
                                <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-5 text-center">
                        <div class="mb-3">
                            <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                        </div>
                        <p class="text-muted mb-0">Aucune notification</p>
                        <small class="text-muted">Vous êtes à jour !</small>
                    </div>
                    @endforelse
                </div>
                @if(auth()->user()->unreadNotifications->count() > 0)
                <div class="p-3 border-top text-center bg-light" style="border-radius: 0 0 0.75rem 0.75rem;">
                    <a href="{{ route('notifications.index') }}" class="text-decoration-none small fw-semibold text-primary">
                        Voir toutes les notifications <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- User Dropdown --}}
        <div class="dropdown">
            <button class="btn btn-light border-0 d-flex align-items-center gap-2 px-2" type="button" data-bs-toggle="dropdown" style="padding: 0.375rem 0.5rem;">
                <div class="user-avatar-sm bg-gradient text-white" style="background: linear-gradient(135deg, var(--primary-color), #7c3aed);">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="d-none d-xl-block text-start lh-sm">
                    @php
                        $role = auth()->user()->roles->first();
                        $roleLabels = [
                            'admin' => 'Administrateur',
                            'supervisor' => 'Superviseur OKAMI',
                            'owner' => 'Propriétaire',
                            'driver' => 'Motard',
                            'cashier' => 'Caissier',
                            'collector' => 'Collecteur',
                        ];
                        $roleColors = [
                            'admin' => 'primary',
                            'supervisor' => 'info',
                            'owner' => 'warning',
                            'driver' => 'success',
                            'cashier' => 'danger',
                            'collector' => 'secondary',
                        ];
                    @endphp
                    <span class="fw-semibold text-dark d-block" style="font-size: 0.875rem;">{{ auth()->user()->name }}</span>
                    <span class="badge badge-soft-{{ $roleColors[$role->name] ?? 'secondary' }}" style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">{{ $roleLabels[$role->name] ?? 'Utilisateur' }}</span>
                </div>
                <i class="bi bi-chevron-down d-none d-xl-block text-muted" style="font-size: 0.625rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow mt-2" style="min-width: 220px;">
                <li class="px-3 py-3 border-bottom bg-light" style="border-radius: 0.75rem 0.75rem 0 0;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar-sm bg-gradient text-white flex-shrink-0" style="background: linear-gradient(135deg, var(--primary-color), #7c3aed); width: 44px; height: 44px; font-size: 1rem;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <strong class="d-block text-truncate">{{ auth()->user()->name }}</strong>
                            <small class="text-muted text-truncate d-block">{{ auth()->user()->email }}</small>
                        </div>
                    </div>
                </li>
                <li class="p-2">
                    <a class="dropdown-item py-2 rounded" href="{{ route('profile.show') }}">
                        <i class="bi bi-person me-2 text-primary"></i>Mon Profil
                    </a>
                </li>
                <li class="px-2">
                    <a class="dropdown-item py-2 rounded" href="{{ route('profile.settings') }}">
                        <i class="bi bi-gear me-2 text-secondary"></i>Paramètres
                    </a>
                </li>
                <li><hr class="dropdown-divider my-2"></li>
                <li class="p-2">
                    <form method="POST" action="{{ route('logout') }}" class="d-inline w-100">
                        @csrf
                        <button type="submit" class="dropdown-item py-2 rounded text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- header @e -->
