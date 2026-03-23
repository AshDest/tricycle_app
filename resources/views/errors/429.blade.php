@extends('errors.layout')

@section('title', 'Trop de requêtes')

@section('content')
    <div class="error-icon" style="background: #fef2f2;">
        <i class="bi bi-hourglass-split" style="color: #ef4444;"></i>
    </div>
    <div class="error-code">429</div>
    <h1 class="error-title">Trop de requêtes</h1>
    <p class="error-message">
        Vous avez effectué trop de requêtes en peu de temps.
        Veuillez patienter quelques instants avant de réessayer.
    </p>
@endsection

