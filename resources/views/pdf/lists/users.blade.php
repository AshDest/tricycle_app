@extends('pdf.lists.layout')

@section('content')
    @if(isset($filtres) && (($filtres['search'] ?? '') || ($filtres['role'] ?? '')))
        <div class="filters-info">
            <strong>Filtres appliqués:</strong>
            @if($filtres['search'] ?? '') Recherche: "{{ $filtres['search'] }}" @endif
            @if($filtres['role'] ?? '') | Rôle: {{ ucfirst($filtres['role']) }} @endif
        </div>
    @endif

    @if(isset($stats))
        <div class="stats-row">
            <div class="stat-item">
                <div class="value">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="label">Total Utilisateurs</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['admins'] ?? 0) }}</div>
                <div class="label">Admins</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['supervisors'] ?? 0) }}</div>
                <div class="label">Superviseurs</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['actifs'] ?? 0) }}</div>
                <div class="label">Actifs</div>
            </div>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 25%">Nom</th>
                <th style="width: 25%">Email</th>
                <th style="width: 15%">Téléphone</th>
                <th style="width: 15%">Rôle</th>
                <th style="width: 15%">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $index => $user)
                <tr class="no-break">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $user->name ?? '-' }}</td>
                    <td style="font-size: 8px;">{{ $user->email ?? '-' }}</td>
                    <td>{{ $user->telephone ?? '-' }}</td>
                    <td>
                        @php
                            $roles = $user->getRoleNames()->toArray();
                            $roleLabels = [
                                'admin' => 'Administrateur',
                                'supervisor' => 'Superviseur',
                                'caissier' => 'Caissier',
                                'collecteur' => 'Collecteur',
                                'motard' => 'Motard',
                                'proprietaire' => 'Propriétaire',
                            ];
                        @endphp
                        @foreach($roles as $role)
                            <span class="badge badge-info">{{ $roleLabels[$role] ?? ucfirst($role) }}</span>
                        @endforeach
                    </td>
                    <td>
                        <span class="badge {{ ($user->is_active ?? true) ? 'badge-success' : 'badge-danger' }}">
                            {{ ($user->is_active ?? true) ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Aucun utilisateur trouvé</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection

