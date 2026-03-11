<div>
    <!-- Header -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">🎁 Nouvelle Récompense</h4>
            <p class="text-muted mb-0">Attribuer une récompense manuelle à un motard</p>
        </div>
        <a href="{{ route('admin.recompenses.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Retour
        </a>
    </div>

    <div class="row">
        <!-- Formulaire -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form wire:submit="submit">
                        <!-- Motard -->
                        <div class="mb-3">
                            <label class="form-label">Motard <span class="text-danger">*</span></label>
                            <select wire:model.live="motard_id" class="form-select @error('motard_id') is-invalid @enderror">
                                <option value="">-- Sélectionner un motard --</option>
                                @foreach($motards as $motard)
                                    <option value="{{ $motard->id }}">{{ $motard->user?->name ?? 'N/A' }} ({{ $motard->identifiant }})</option>
                                @endforeach
                            </select>
                            @error('motard_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <!-- Type -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type de Récompense <span class="text-danger">*</span></label>
                                <select wire:model.live="type" class="form-select @error('type') is-invalid @enderror">
                                    @foreach($types as $key => $type)
                                        <option value="{{ $key }}">{{ $type['label'] }}</option>
                                    @endforeach
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Catégorie -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                                <select wire:model="categorie" class="form-select @error('categorie') is-invalid @enderror">
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('categorie') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Titre -->
                        <div class="mb-3">
                            <label class="form-label">Titre <span class="text-danger">*</span></label>
                            <input type="text" wire:model="titre" class="form-control @error('titre') is-invalid @enderror" placeholder="Ex: Badge Or - Mars 2026">
                            @error('titre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea wire:model="description" class="form-control" rows="2" placeholder="Description de la récompense..."></textarea>
                        </div>

                        <div class="row">
                            <!-- Période -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Début période <span class="text-danger">*</span></label>
                                <input type="date" wire:model="periode_debut" class="form-control @error('periode_debut') is-invalid @enderror">
                                @error('periode_debut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Fin période <span class="text-danger">*</span></label>
                                <input type="date" wire:model="periode_fin" class="form-control @error('periode_fin') is-invalid @enderror">
                                @error('periode_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <!-- Prime (si applicable) -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Montant Prime (FC)</label>
                                <input type="number" wire:model="montant_prime" class="form-control" placeholder="0" min="0">
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label class="form-label">Notes internes</label>
                            <textarea wire:model="notes" class="form-control" rows="2" placeholder="Notes pour l'administration..."></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.recompenses.index') }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Attribuer la Récompense
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Aperçu Performance -->
        <div class="col-lg-4">
            @if($motardSelectionne)
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>{{ $motardSelectionne->user?->name ?? 'N/A' }}</h6>
                </div>
                <div class="card-body">
                    @if($performanceMotard)
                        <h6 class="text-muted mb-3">Performance du mois</h6>

                        <!-- Score Total -->
                        <div class="text-center mb-4">
                            <div class="display-4 fw-bold {{ $performanceMotard->score_class }}">
                                {{ $performanceMotard->score_total }}/100
                            </div>
                            @if($performanceMotard->badge !== 'aucun')
                                <span class="badge fs-6" style="background-color: {{ $performanceMotard->badge_color }}">
                                    {{ ucfirst($performanceMotard->badge) }}
                                </span>
                            @endif
                        </div>

                        <!-- Détails -->
                        <div class="mb-3">
                            <label class="form-label small">Régularité</label>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" style="width: {{ $performanceMotard->score_regularite }}%">
                                    {{ $performanceMotard->score_regularite }}%
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Sécurité</label>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-info" style="width: {{ $performanceMotard->score_securite }}%">
                                    {{ $performanceMotard->score_securite }}%
                                </div>
                            </div>
                            @if($performanceMotard->accidents_total > 0)
                                <small class="text-danger">{{ $performanceMotard->accidents_total }} accident(s)</small>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Versement</label>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-warning" style="width: {{ $performanceMotard->score_versement }}%">
                                    {{ $performanceMotard->score_versement }}%
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="small">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Jours travaillés:</span>
                                <strong>{{ $performanceMotard->jours_travailles }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Versements à temps:</span>
                                <strong class="text-success">{{ $performanceMotard->versements_a_temps }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Versements en retard:</span>
                                <strong class="text-danger">{{ $performanceMotard->versements_en_retard }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Total versé:</span>
                                <strong>{{ number_format($performanceMotard->total_verse) }} FC</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Arriérés:</span>
                                <strong class="{{ $performanceMotard->arrieres_cumules > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($performanceMotard->arrieres_cumules) }} FC
                                </strong>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-hourglass display-6"></i>
                            <p class="mt-2">Calcul des performances...</p>
                        </div>
                    @endif
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    <i class="bi bi-person-circle display-4"></i>
                    <p class="mt-3">Sélectionnez un motard pour voir ses performances</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

