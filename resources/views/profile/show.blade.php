<x-dashlite-layout>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-person-circle me-2 text-primary"></i>Mon Profil
            </h4>
            <p class="text-muted mb-0">Informations de votre compte</p>
        </div>
        <div>
            <a href="{{ route('profile.settings') }}" class="btn btn-primary">
                <i class="bi bi-gear me-2"></i>Paramètres
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Profile Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5" style="background: linear-gradient(135deg, #1e3a5f 0%, #0d253f 100%); border-radius: 0.5rem;">
                    <div class="avatar avatar-xl mx-auto mb-4" style="width: 100px; height: 100px; background: linear-gradient(135deg, #f59e0b, #d97706); font-size: 2.5rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; border: 4px solid rgba(255,255,255,0.3);">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <h4 class="fw-bold mb-1 text-white">{{ auth()->user()->name }}</h4>
                    <p class="text-light mb-3">{{ auth()->user()->email }}</p>

                    @php
                        $role = auth()->user()->roles->first();
                        $roleLabels = [
                            'admin' => 'Administrateur LATEM',
                            'supervisor' => 'Superviseur OKAMI',
                            'owner' => 'Propriétaire',
                            'driver' => 'Motard',
                            'cashier' => 'Caissier',
                            'collector' => 'Collecteur',
                            'cleaner' => 'Laveur',
                        ];
                        $roleBgColors = [
                            'admin' => '#4f46e5',
                            'supervisor' => '#0891b2',
                            'owner' => '#d97706',
                            'driver' => '#059669',
                            'cashier' => '#dc2626',
                            'collector' => '#6366f1',
                            'cleaner' => '#8b5cf6',
                        ];
                        $roleName = $role ? $role->name : 'user';
                    @endphp
                    <span class="badge px-3 py-2" style="background-color: {{ $roleBgColors[$roleName] ?? '#6b7280' }}; color: white; font-size: 0.875rem;">
                        <i class="bi bi-shield-check me-1"></i>{{ $roleLabels[$roleName] ?? 'Utilisateur' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="col-lg-8">
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header py-3 bg-white border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person me-2 text-primary"></i>Informations Personnelles</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Nom complet</label>
                            <p class="fw-semibold mb-0 text-dark fs-6">{{ auth()->user()->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Adresse email</label>
                            <p class="fw-semibold mb-0 text-dark fs-6">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Rôle</label>
                            <p class="fw-semibold mb-0 text-dark fs-6">{{ $roleLabels[$roleName] ?? 'Utilisateur' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Membre depuis</label>
                            <p class="fw-semibold mb-0 text-dark fs-6">{{ auth()->user()->created_at->translatedFormat('d F Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header py-3 bg-white border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-shield-lock me-2 text-warning"></i>Sécurité</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                        <div>
                            <p class="fw-semibold mb-1 text-dark">Mot de passe</p>
                            <small class="text-muted">Dernière modification: {{ auth()->user()->updated_at->diffForHumans() }}</small>
                        </div>
                        <a href="{{ route('profile.settings') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i>Modifier
                        </a>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-3">
                        <div>
                            <p class="fw-semibold mb-1 text-dark">Email vérifié</p>
                            <small class="text-muted">
                                @if(auth()->user()->email_verified_at)
                                    Vérifié le {{ auth()->user()->email_verified_at->translatedFormat('d F Y') }}
                                @else
                                    Non vérifié
                                @endif
                            </small>
                        </div>
                        @if(auth()->user()->email_verified_at)
                        <span class="badge bg-success px-3 py-2">
                            <i class="bi bi-check-circle me-1"></i>Vérifié
                        </span>
                        @else
                        <span class="badge bg-warning text-dark px-3 py-2">
                            <i class="bi bi-exclamation-circle me-1"></i>Non vérifié
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashlite-layout>
