<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Déclarer un Accident
            </h4>
            <p class="text-muted mb-0">Enregistrer un accident impliquant une moto-tricycle</p>
        </div>
        <a href="{{ route('supervisor.accidents.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form wire:submit="save">
        <div class="row g-4">
            <!-- Informations principales -->
            <div class="col-lg-8">
                <!-- Véhicule et Motard -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-bicycle me-2 text-primary"></i>Véhicule et Motard impliqués</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Moto <span class="text-danger">*</span></label>
                                <select wire:model.live="moto_id" class="form-select @error('moto_id') is-invalid @enderror">
                                    <option value="">-- Sélectionner une moto --</option>
                                    @foreach($motos as $moto)
                                        <option value="{{ $moto->id }}">
                                            {{ $moto->plaque_immatriculation }}
                                            @if($moto->proprietaire) - {{ $moto->proprietaire->user->name ?? '' }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('moto_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Motard impliqué <span class="text-danger">*</span></label>
                                <select wire:model="motard_id" class="form-select @error('motard_id') is-invalid @enderror">
                                    <option value="">-- Sélectionner le motard --</option>
                                    @foreach($motards as $motard)
                                        <option value="{{ $motard->id }}">{{ $motard->user->name ?? 'N/A' }}</option>
                                    @endforeach
                                </select>
                                @error('motard_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Détails de l'accident -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-info"></i>Détails de l'accident</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date et heure <span class="text-danger">*</span></label>
                                <input type="datetime-local" wire:model="date_heure" class="form-control @error('date_heure') is-invalid @enderror">
                                @error('date_heure')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Lieu de l'accident <span class="text-danger">*</span></label>
                                <input type="text" wire:model="lieu" class="form-control @error('lieu') is-invalid @enderror" placeholder="Ex: Avenue Lumumba, Commune de Gombe">
                                @error('lieu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description de l'accident <span class="text-danger">*</span></label>
                                <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="4" placeholder="Décrivez les circonstances de l'accident..."></textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Témoignages -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-chat-quote me-2 text-warning"></i>Témoignages</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Déclaration du motard</label>
                                <textarea wire:model="temoignage_motard" class="form-control" rows="3" placeholder="Témoignage du motard impliqué..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Témoignage externe</label>
                                <textarea wire:model="temoignage_temoin" class="form-control" rows="3" placeholder="Témoignage d'un témoin (si disponible)..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom du témoin</label>
                                <input type="text" wire:model="temoin_nom" class="form-control" placeholder="Nom complet">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Téléphone du témoin</label>
                                <input type="text" wire:model="temoin_telephone" class="form-control" placeholder="+243 XXX XXX XXX">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Évaluation des dommages -->
                <div class="card">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-clipboard-data me-2 text-danger"></i>Évaluation des dommages</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Pièces endommagées</label>
                                <textarea wire:model="pieces_endommagees" class="form-control" rows="2" placeholder="Liste des pièces endommagées (ex: phare avant, garde-boue, rétroviseur gauche...)"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estimation du coût (FC)</label>
                                <input type="number" wire:model="estimation_cout" class="form-control" min="0" step="1000" placeholder="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Prise en charge <span class="text-danger">*</span></label>
                                <select wire:model="prise_en_charge" class="form-select">
                                    @foreach($prisesEnCharge as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Gravité -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-exclamation-circle me-2 text-danger"></i>Gravité</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-2">
                            @foreach($niveauxGravite as $key => $label)
                            <div class="form-check">
                                <input type="radio" wire:model="gravite" value="{{ $key }}" class="form-check-input" id="gravite_{{ $key }}">
                                <label class="form-check-label" for="gravite_{{ $key }}">
                                    @php
                                        $colors = ['mineur' => 'success', 'modere' => 'warning', 'grave' => 'danger'];
                                    @endphp
                                    <span class="badge badge-soft-{{ $colors[$key] }}">{{ $label }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Statut -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-gear me-2 text-secondary"></i>Statut</h6>
                    </div>
                    <div class="card-body">
                        <select wire:model="statut" class="form-select">
                            <option value="declare">Déclaré</option>
                            <option value="evalue">Évalué</option>
                            <option value="reparation_programmee">Réparation programmée</option>
                            <option value="repare">Réparé</option>
                            <option value="cloture">Clôturé</option>
                        </select>
                    </div>
                </div>

                <!-- Notes admin -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-sticky me-2 text-warning"></i>Notes administrateur</h6>
                    </div>
                    <div class="card-body">
                        <textarea wire:model="notes_admin" class="form-control" rows="4" placeholder="Notes internes..."></textarea>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-danger w-100 mb-2" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                <i class="bi bi-check-lg me-1"></i>Enregistrer l'accident
                            </span>
                            <span wire:loading wire:target="save">
                                <span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...
                            </span>
                        </button>
                        <a href="{{ route('supervisor.accidents.index') }}" class="btn btn-outline-secondary w-100">
                            Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

