<!-- header @s -->
<div class="app-header">
    <!-- Mobile Toggle -->
    <button class="btn btn-sm btn-light d-lg-none me-2" onclick="toggleSidebar()">
        <i class="bi bi-list fs-5"></i>
    </button>

    <!-- Page Title (mobile) -->
    <div class="d-lg-none fw-semibold text-dark">
        @yield('title', 'Dashboard')
    </div>

    <!-- Search -->
    <div class="d-none d-lg-block flex-grow-1" style="max-width: 400px;">
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-light border-end-0">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" class="form-control form-control-sm bg-light border-start-0" placeholder="Rechercher...">
        </div>
    </div>

    <!-- Spacer -->
    <div class="flex-grow-1"></div>

    <!-- Header Tools -->
    <div class="d-flex align-items-center gap-2">
        {{-- Notifications --}}
        <div class="dropdown">
            <button class="btn btn-sm btn-light position-relative" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                <i class="bi bi-bell fs-5"></i>
                @if(auth()->user()->unreadNotifications->count() > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                    {{ auth()->user()->unreadNotifications->count() }}
                </span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-0" style="width: 340px;">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Notifications</h6>
                    <a href="#" class="text-decoration-none small text-primary">Tout marquer lu</a>
                </div>
                <div style="max-height: 320px; overflow-y: auto;">
                    @forelse(auth()->user()->unreadNotifications ?? [] as $notification)
                    <div class="px-3 py-2 border-bottom hover-bg-light">
                        <div class="d-flex gap-2 align-items-start">
                            <div class="rounded-circle bg-{{ $notification->data['color'] ?? 'primary' }} bg-opacity-10 p-2 flex-shrink-0">
                                <i class="bi bi-{{ $notification->data['icon'] ?? 'bell' }} text-{{ $notification->data['color'] ?? 'primary' }}"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <p class="mb-0 small text-dark">{{ $notification->data['message'] ?? '' }}</p>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 text-center">
                        <i class="bi bi-bell-slash text-muted fs-3 d-block mb-2"></i>
                        <p class="text-muted small mb-0">Aucune notification</p>
                    </div>
                    @endforelse
                </div>
                <div class="p-2 border-top text-center">
                    <a href="{{ route('notifications.index') }}" class="text-decoration-none small fw-medium">Voir toutes les notifications</a>
                </div>
            </div>
        </div>

        {{-- User Dropdown --}}
        <div class="dropdown">
            <button class="btn btn-sm btn-light d-flex align-items-center gap-2 px-2" type="button" data-bs-toggle="dropdown">
                <div class="user-avatar-sm bg-primary bg-gradient text-white">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="d-none d-xl-block text-start lh-sm">
                    @php
                        $role = auth()->user()->roles->first();
                        $roleLabels = [
                            'admin' => 'Administrateur',
                            'supervisor' => 'OKAMI',
                            'owner' => 'Propri&eacute;taire',
                            'driver' => 'Motard',
                            'cashier' => 'Caissier',
                            'collector' => 'Collecteur',
                        ];
                    @endphp
                    <small class="text-muted d-block" style="font-size: 0.7rem;">{{ $roleLabels[$role->name] ?? 'Utilisateur' }}</small>
                    <span class="fw-medium text-dark" style="font-size: 0.8125rem;">{{ auth()->user()->name }}</span>
                </div>
                <i class="bi bi-chevron-down d-none d-xl-block" style="font-size: 0.625rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2" style="min-width: 200px;">
                <li class="px-3 py-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="user-avatar-sm bg-primary bg-gradient text-white flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <strong class="d-block small text-truncate">{{ auth()->user()->name }}</strong>
                            <small class="text-muted text-truncate d-block">{{ auth()->user()->email }}</small>
                        </div>
                    </div>
                </li>
                <li><a class="dropdown-item py-2" href="{{ route('profile.show') }}"><i class="bi bi-person me-2"></i>Mon Profil</a></li>
                <li><a class="dropdown-item py-2" href="{{ route('profile.settings') }}"><i class="bi bi-gear me-2"></i>Param&egrave;tres</a></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline w-100">
                        @csrf
                        <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-box-arrow-right me-2"></i>D&eacute;connexion</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- header @e -->
