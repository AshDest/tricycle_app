@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Mon Profil</h5>
                </div>
                <div class="card-body">
                    <div class="user-info-card">
                        <div class="user-avatar user-avatar-xl">
                            <span>{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                        </div>
                        <div class="user-details">
                            <h5>{{ auth()->user()->name }}</h5>
                            <p class="text-muted">{{ auth()->user()->email }}</p>
                            @if(auth()->user()->phone)
                                <p class="text-muted">{{ auth()->user()->phone }}</p>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informations de Base</h6>
                            <p><strong>Nom:</strong> {{ auth()->user()->name }}</p>
                            <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Rôle</h6>
                            @php
                                $role = auth()->user()->roles->first();
                                $roleLabels = [
                                    'admin' => 'Administrateur NTH',
                                    'supervisor' => 'OKAMI',
                                    'owner' => 'Propriétaire',
                                    'driver' => 'Motard',
                                    'cashier' => 'Caissier',
                                    'collector' => 'Collecteur',
                                ];
                            @endphp
                            <p>{{ $roleLabels[$role->name] ?? 'Utilisateur' }}</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('profile.settings') }}" class="btn btn-primary">Modifier les paramètres</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
