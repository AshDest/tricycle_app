<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Déclarer un Accident
            </h4>
            <p class="text-muted mb-0">Signalez un incident impliquant votre moto</p>
        </div>
        <a href="{{ route('driver.historique') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">
        <!-- Formulaire -->
        <div class="col-lg-8">
            <form wire:submit="save">
                <!-- Informations de base -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i>Informations de l'Accident</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <!-- Moto concernée -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Moto concernée <span class="text-danger">*</span></label>
                                <select wire:model="moto_id" class="form-select @error('moto_id') is-invalid @enderror" required>
                                    <option value="">-- Sélectionner --</option>
                                    @if($moto)
                                    <option value="{{ $moto->id }}" selected>{{ $moto->plaque_immatriculation }} - {{ $moto->numero_matricule }}</option>
                                    @endif
                                </select>
                                @error('moto_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Date et heure -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date et heure <span class="text-danger">*</span></label>
                                <input type="datetime-local" wire:model="date_heure" class="form-control @error('date_heure') is-invalid @enderror" required>
                                @error('date_heure')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Lieu -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Lieu de l'accident <span class="text-danger">*</span></label>
                                <input type="text" wire:model="lieu" class="form-control @error('lieu') is-invalid @enderror"
                                       placeholder="Ex: Avenue Kasa-Vubu, près du marché central" required>
                                @error('lieu')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Gravité -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Gravité <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" wire:model="gravite" value="mineur" id="graviteMineur">
                                        <label class="form-check-label" for="graviteMineur">
                                            <span class="badge bg-info">Mineur</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" wire:model="gravite" value="modere" id="graviteModere">
                                        <label class="form-check-label" for="graviteModere">
                                            <span class="badge bg-warning">Modéré</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" wire:model="gravite" value="grave" id="graviteGrave">
                                        <label class="form-check-label" for="graviteGrave">
                                            <span class="badge bg-danger">Grave</span>
                                        </label>
                                    </div>
                                </div>
                                @error('gravite')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Estimation coût -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estimation du coût de réparation</label>
                                <div class="input-group">
                                    <input type="number" wire:model="estimation_cout" class="form-control @error('estimation_cout') is-invalid @enderror"
                                           placeholder="0" min="0">
                                    <span class="input-group-text">FC</span>
                                </div>
                                @error('estimation_cout')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description et témoignage -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-chat-left-text me-2 text-info"></i>Description et Témoignage</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <!-- Description -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description de l'accident <span class="text-danger">*</span></label>
                                <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror"
                                          rows="4" placeholder="Décrivez les circonstances de l'accident..." required></textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Témoignage du motard -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Votre témoignage <span class="text-danger">*</span></label>
                                <textarea wire:model="temoignage_motard" class="form-control @error('temoignage_motard') is-invalid @enderror"
                                          rows="3" placeholder="Décrivez ce qui s'est passé de votre point de vue..." required></textarea>
                                @error('temoignage_motard')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Témoin (optionnel) -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-person-check me-2 text-secondary"></i>Témoin (optionnel)</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom du témoin</label>
                                <input type="text" wire:model="temoin_nom" class="form-control" placeholder="Nom complet du témoin">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Téléphone du témoin</label>
                                <input type="tel" wire:model="temoin_telephone" class="form-control" placeholder="Ex: +243 XXX XXX XXX">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Boutons -->
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-danger btn-lg flex-grow-1" wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="bi bi-exclamation-triangle me-2"></i>Déclarer l'Accident
                        </span>
                        <span wire:loading>
                            <span class="spinner-border spinner-border-sm me-2"></span>Envoi en cours...
                        </span>
                    </button>
                    <a href="{{ route('driver.historique') }}" class="btn btn-outline-secondary btn-lg">
                        Annuler
                    </a>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Info moto -->
            @if($moto)
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-bicycle me-2 text-primary"></i>Ma Moto</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-flex mb-2">
                            <i class="bi bi-bicycle text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-1">{{ $moto->plaque_immatriculation }}</h5>
                        <p class="text-muted small mb-0">{{ $moto->numero_matricule }}</p>
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Statut</span>
                            <span class="badge badge-soft-{{ $moto->statut === 'actif' ? 'success' : 'warning' }}">
                                {{ ucfirst($moto->statut) }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
            @endif

            <!-- Instructions -->
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-lightbulb me-2 text-warning"></i>Important</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning border-0 mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Rappel :</strong> Déclarez l'accident le plus rapidement possible.
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-check-circle text-success mt-1"></i>
                            <span>Soyez précis sur le lieu et l'heure</span>
                        </li>
                        <li class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-check-circle text-success mt-1"></i>
                            <span>Décrivez les dommages constatés</span>
                        </li>
                        <li class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-check-circle text-success mt-1"></i>
                            <span>Notez les coordonnées des témoins si possible</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-info-circle text-info mt-1"></i>
                            <span>Vous ne pouvez pas modifier les coûts après soumission</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
