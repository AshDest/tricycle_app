<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Caissiers</h2>
        <a href="{{ route('admin.caissiers.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Ajouter Caissier
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Nom</th>
                            <th>Point Collecte</th>
                            <th>Zone</th>
                            <th>Solde</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($caissiers as $caissier)
                            <tr>
                                <td><strong>{{ $caissier->user->name }}</strong></td>
                                <td>{{ $caissier->nom_point_collecte }}</td>
                                <td>{{ $caissier->zone }}</td>
                                <td>{{ number_format($caissier->solde_actuel, 2) }} FC</td>
                                <td>
                                    <span class="badge {{ $caissier->is_active ? 'bg-success' : 'bg-danger' }}" style="cursor: pointer;" wire:click="toggleActive({{ $caissier->id }})">
                                        {{ $caissier->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.caissiers.show', $caissier->id) }}" class="btn btn-info btn-sm" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.caissiers.edit', $caissier->id) }}" class="btn btn-warning btn-sm" title="Éditer">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn btn-danger btn-sm" wire:click="delete({{ $caissier->id }})" onclick="return confirm('Êtes-vous sûr?')" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Aucun caissier trouvé</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $caissiers->links() }}
            </div>
        </div>
    </div>
</div>
