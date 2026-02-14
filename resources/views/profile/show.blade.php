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
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="avatar avatar-xl mx-auto mb-4" style="width: 100px; height: 100px; background: linear-gradient(135deg, #4f46e5, #7c3aed); font-size: 2.5rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <h4 class="fw-bold mb-1">{{ auth()->user()->name }}</h4>
                    <p class="text-muted mb-3">{{ auth()->user()->email }}</p>

                    @php
                        $role = auth()->user()->roles->first();
                        $roleLabels = [
                            'admin' => 'Administrateur NTH',
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
                    <span class="badge badge-soft-{{ $roleColors[$role->name] ?? 'secondary' }} px-3 py-2">
                        <i class="bi bi-shield-check me-1"></i>{{ $roleLabels[$role->name] ?? 'Utilisateur' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-person me-2 text-primary"></i>Informations Personnelles</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Nom complet</label>
                            <p class="fw-semibold mb-0">{{ auth()->user()->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Adresse email</label>
                            <p class="fw-semibold mb-0">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Rôle</label>
                            <p class="fw-semibold mb-0">{{ $roleLabels[$role->name] ?? 'Utilisateur' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Membre depuis</label>
                            <p class="fw-semibold mb-0">{{ auth()->user()->created_at->translatedFormat('d F Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2 text-warning"></i>Sécurité</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <p class="fw-medium mb-1">Mot de passe</p>
                            <small class="text-muted">Dernière modification: {{ auth()->user()->updated_at->diffForHumans() }}</small>
                        </div>
                        <a href="{{ route('profile.settings') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i>Modifier
                        </a>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <div>
                            <p class="fw-medium mb-1">Email vérifié</p>
                            <small class="text-muted">
                                @if(auth()->user()->email_verified_at)
                                    Vérifié le {{ auth()->user()->email_verified_at->translatedFormat('d F Y') }}
                                @else
                                    Non vérifié
                                @endif
                            </small>
                        </div>
                        @if(auth()->user()->email_verified_at)
                        <span class="badge badge-soft-success">
                            <i class="bi bi-check-circle me-1"></i>Vérifié
                        </span>
                        @else
                        <span class="badge badge-soft-warning">
                            <i class="bi bi-exclamation-circle me-1"></i>Non vérifié
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashlite-layout>
