#!/bin/bash

# Convert caissiers index
cat > resources/views/livewire/admin/caissiers/index.blade.php << 'TEMPLATE'
<div>
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">List des Caissiers</h2>
                <p class="text-muted mb-0">Gestion des caissiers du système</p>
            </div>
            <div>
                <a href="{{ route('admin.caissiers.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus"></i> Ajouter Caissier
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead>
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
                            <td>{{ number_format($caissier->solde_actuel, 2) }} FCFA</td>
                            <td>
                                <span class="badge {{ $caissier->is_active ? 'bg-success' : 'bg-danger' }}" wire:click="toggleActive({{ $caissier->id }})" style="cursor: pointer;">
                                    {{ $caissier->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.caissiers.show', $caissier->id) }}" class="btn btn-outline-primary" title="Voir">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.caissiers.edit', $caissier->id) }}" class="btn btn-outline-primary" title="Éditer">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="#" wire:click="delete({{ $caissier->id }})" onclick="return confirm('Êtes-vous sûr?')" class="btn btn-outline-danger" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Aucun caissier trouvé</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $caissiers->links() }}
        </div>
    </div>
</div>
TEMPLATE

echo "Converted caissiers index"
