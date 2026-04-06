<div>
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1"><i class="bi bi-wallet2 me-2 text-success"></i>Soldes & Dépenses des Collecteurs</h4>
            <p class="text-muted mb-0">Vue globale des soldes et suivi quotidien des dépenses</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="exportPdf" class="btn btn-danger"><i class="bi bi-file-pdf me-1"></i>Exporter PDF</button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                <div class="card-body text-white py-3">
                    <p class="mb-1 opacity-75 small">Solde Caisse Total</p>
                    <h4 class="fw-bold mb-0">{{ number_format($totalSoldeCaisse) }} FC</h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);">
                <div class="card-body text-white py-3">
                    <p class="mb-1 opacity-75 small">Paiements (Période)</p>
                    <h4 class="fw-bold mb-0">{{ number_format($totalPaiementsPeriode) }} FC</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Rechercher</label>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Nom, identifiant...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Zone</label>
                    <select wire:model.live="filterZone" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($zones ?? [] as $zone)
                        <option value="{{ $zone }}">{{ $zone }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Du</label>
                    <input type="date" wire:model.live="dateDebut" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Au</label>
                    <input type="date" wire:model.live="dateFin" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Collecteur</th>
                            <th>Zone</th>
                            <th class="text-end">Solde Caisse</th>
                            <th class="text-end">Collectes (Période)</th>
                            <th class="text-end">Dépenses (Période)</th>
                            <th class="text-center pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($collecteurs as $collecteur)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar-sm bg-info bg-opacity-10 text-info">
                                        {{ strtoupper(substr($collecteur->user->name ?? 'C', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $collecteur->user->name ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $collecteur->numero_identifiant ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $collecteur->zone_affectation ?? 'N/A' }}</span></td>
                            <td class="text-end fw-bold" style="color: #4f46e5;">{{ number_format($collecteur->solde_caisse ?? 0) }} FC</td>
                            <td class="text-end">
                                @if(($collecteur->collectes_periode ?? 0) > 0)
                                    <span class="text-success fw-semibold">+{{ number_format($collecteur->collectes_periode) }} FC</span>
                                @else
                                    <span class="text-muted">0 FC</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(($collecteur->depenses_periode ?? 0) > 0)
                                    <span class="text-danger fw-semibold">-{{ number_format($collecteur->depenses_periode) }} FC</span>
                                @else
                                    <span class="text-muted">0 FC</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <a href="{{ route('admin.collecteurs.solde', $collecteur) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-journal-text me-1"></i>Détails
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun collecteur actif trouvé</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($collecteurs->count() > 0)
                    <tfoot class="bg-light fw-bold">
                        <tr>
                            <td class="ps-4" colspan="2">TOTAUX</td>
                            <td class="text-end" style="color: #4f46e5;">{{ number_format($totalSoldeCaisse) }} FC</td>
                            <td class="text-end text-success">+{{ number_format($collecteurs->sum('collectes_periode')) }} FC</td>
                            <td class="text-end text-danger">-{{ number_format($collecteurs->sum('depenses_periode')) }} FC</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
        @if($collecteurs->hasPages())
        <div class="card-footer bg-light">
            {{ $collecteurs->links() }}
        </div>
        @endif
    </div>
</div>
