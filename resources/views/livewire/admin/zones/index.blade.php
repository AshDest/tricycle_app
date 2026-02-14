<div>
    @section('title', 'Zones')
    <div class="page-header d-flex justify-content-between align-items-center">
        <div><h4>Zones</h4><p class="text-muted small mb-0">Gestion des zones g&eacute;ographiques</p></div>
        <a href="{{ route('admin.zones.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nouvelle Zone</a>
    </div>
    <div class="card">
        <div class="card-header">
            <div class="row g-2">
                <div class="col-md-4"><input type="text" wire:model.live="search" class="form-control form-control-sm" placeholder="Rechercher..."></div>
                <div class="col-md-3"><select wire:model.live="filterStatut" class="form-select form-select-sm"><option value="">Tous</option><option value="1">Actif</option><option value="0">Inactif</option></select></div>
                <div class="col-md-2"><select wire:model.live="perPage" class="form-select form-select-sm"><option value="10">10</option><option value="25">25</option><option value="50">50</option></select></div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Nom</th><th>Description</th><th>Communes</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                        @forelse($zones as $zone)
                        <tr>
                            <td class="fw-medium">{{ $zone->nom }}</td>
                            <td class="text-muted small">{{ Str::limit($zone->description, 50) }}</td>
                            <td class="small">{{ $zone->communes }}</td>
                            <td><span class="badge bg-{{ $zone->is_active ? 'success' : 'secondary' }}">{{ $zone->is_active ? 'Actif' : 'Inactif' }}</span></td>
                            <td class="text-end">
                                <button wire:click="toggleActive({{ $zone->id }})" class="btn btn-sm btn-outline-{{ $zone->is_active ? 'warning' : 'success' }}"><i class="bi bi-{{ $zone->is_active ? 'pause' : 'play' }}"></i></button>
                                <a href="{{ route('admin.zones.edit', $zone) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <button wire:click="delete({{ $zone->id }})" wire:confirm="Supprimer cette zone ?" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Aucune zone</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($zones->hasPages())<div class="card-footer">{{ $zones->links() }}</div>@endif
    </div>
</div>
