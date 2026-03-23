@extends('errors.layout')

@section('title', 'Session expirée')

@section('content')
    <div class="error-icon" style="background: #f0fdf4;">
        <i class="bi bi-clock-history" style="color: #22c55e;"></i>
    </div>
    <div class="error-code">419</div>
    <h1 class="error-title">Session expirée</h1>
    <p class="error-message">
        Votre session a expiré pour des raisons de sécurité.
        Veuillez actualiser la page et réessayer.
    </p>
@endsection

