<x-dashlite-layout>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-gear me-2 text-secondary"></i>Paramètres du Profil
            </h4>
            <p class="text-muted mb-0">Modifiez vos informations personnelles</p>
        </div>
        <div>
            <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Retour au profil
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Update Profile Information -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-person me-2 text-primary"></i>Informations Personnelles</h6>
                </div>
                <div class="card-body">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>
        </div>

        <!-- Update Password -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2 text-warning"></i>Changer le Mot de Passe</h6>
                </div>
                <div class="card-body">
                    <livewire:profile.update-password-form />
                </div>
            </div>
        </div>
    </div>
</x-dashlite-layout>
