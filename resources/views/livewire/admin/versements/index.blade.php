<div>
    @section('title', 'Versements')
    <div class="page-header d-flex justify-content-between align-items-center">
        <div><h4>Versements</h4><p class="text-muted small mb-0">Suivi des versements journaliers</p></div>
    </div>
    <div class="card">
        <div class="card-header">
            <div class="row g-2 align-items-end">
                <div class="col-md-3"><input type="text" wire:model.live="search" class="form-control form-control-sm" placeholder="Rechercher..."></div>
                <div class="col-md-2"><select wire:model.live="filterStatut" class="form-select form-select-sm"><option value="">Tous statuts</option><option value="paye">Pay&eacute;</option><option value="partiel">Partiel</option><option value="en_retard">En retard</option><option value="non_paye">Non pay&eacute;</option></select></div>
                <div class="col-md-2"><select wire:model.live="filterMode" class="form-select form-select-sm"><option value="">Tous modes</option><option value="especes">Esp&egrave;ces</option><option value="mobile_money">Mobile Money</option><option value="virement">Virement</option></select></div>
                <div class="col-md-2"><input type="date" wire:model.live="dateFrom" class="form-control form-control-sm"></div>
                <div class="col-md-2"><input type="date" wire:model.live="dateTo" class="form-control form-control-sm"></div>
                <div class="col-md-1"><button wire:click="resetFilters" class="btn btn-sm btn-outline-secondary w-100" title="R&eacute;initialiser"><i class="bi bi-x-lg"></i></button></div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Motard</th><th>Moto</th><th>Montant</th><th>Attendu</th><th>Mode</th><th>Statut</th><th>Date</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                        @forelse($versements as $versement)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar-sm bg-primary bg-opacity-10 text-primary">{{ strtoupper(substr($versement->motard->user->name ?? 'N', 0, 1)) }}</div>
                                    <span class="fw-medium">{{ $versement->motard->user->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="small">{{ $versement->moto->plaque_immatriculation ?? 'N/A' }}</td>
                            <td class="fw-semibold">{{ number_format($versement->montant) }} FCFA</td>
                            <td class="text-muted">{{ number_format($versement->montant_attendu) }} FCFA</td>
                            <td><span class="badge bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $versement->mode_paiement ?? '-')) }}</span></td>
                            <td>
                                @php $colors = ['paye' => 'success', 'partiel' => 'warning', 'en_retard' => 'danger', 'non_paye' => 'secondary']; @endphp
                                <span class="badge bg-{{ $colors[$versement->statut] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $versement->statut)) }}</span>
                            </td>
                            <td class="text-muted small">{{ $versement->date_versement?->format('d/m/Y') ?? '-' }}</td>
                            <td class="text-end"><a href="{{ route('admin.versements.show', $versement) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-4"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Aucun versement</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($versements->hasPages())<div class="card-footer">{{ $versements->links() }}</div>@endif
    </div>
</div>
