<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-gear me-2 text-secondary"></i>Paramètres du Système
            </h4>
            <p class="text-muted mb-0">Configuration générale de l'application</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">
        <!-- Paramètres Généraux -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-building me-2 text-primary"></i>Informations Générales</h6>
                </div>
                <div class="card-body">
                    <form wire:submit="save">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nom de l'application</label>
                            <input type="text" wire:model="app_name" class="form-control" placeholder="Tricycle App">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email de contact</label>
                            <input type="email" wire:model="app_email" class="form-control" placeholder="contact@nth.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Téléphone</label>
                            <input type="text" wire:model="app_phone" class="form-control" placeholder="+243 XXX XXX XXX">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Enregistrer
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Paramètres Financiers -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cash-coin me-2 text-success"></i>Paramètres Financiers</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Montant journalier par moto</label>
                        <div class="input-group">
                            <input type="number" class="form-control" value="50000" readonly>
                            <span class="input-group-text">FC</span>
                        </div>
                        <small class="text-muted">Équivalent ~20 USD</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jours de travail par semaine</label>
                        <input type="number" class="form-control" value="5" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Montant hebdomadaire attendu</label>
                        <div class="input-group">
                            <input type="number" class="form-control" value="250000" readonly>
                            <span class="input-group-text">FC</span>
                        </div>
                        <small class="text-muted">Équivalent ~100 USD</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modes de Paiement -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-credit-card me-2 text-info"></i>Modes de Paiement</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-phone me-2"></i>M-PESA</span>
                            <span class="badge bg-success">Actif</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-phone me-2"></i>Airtel Money</span>
                            <span class="badge bg-success">Actif</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-phone me-2"></i>Orange Money</span>
                            <span class="badge bg-success">Actif</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-bank me-2"></i>Virement Bancaire</span>
                            <span class="badge bg-success">Actif</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Informations Système -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-secondary"></i>Informations Système</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Version Laravel</span>
                            <span class="text-muted">{{ app()->version() }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Version PHP</span>
                            <span class="text-muted">{{ phpversion() }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Environnement</span>
                            <span class="badge {{ app()->environment('production') ? 'bg-success' : 'bg-warning' }}">
                                {{ app()->environment() }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Timezone</span>
                            <span class="text-muted">{{ config('app.timezone') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
