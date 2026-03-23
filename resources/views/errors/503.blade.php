@extends('errors.layout')

@section('title', 'Maintenance en cours')

@section('content')
    <div class="error-icon" style="background: #eef2ff;">
        <i class="bi bi-gear" style="color: #4f46e5;"></i>
    </div>
    <div class="error-code">503</div>
    <h1 class="error-title">Maintenance en cours</h1>
    <p class="error-message">
        L'application est temporairement indisponible pour une mise à jour.
        Nous serons de retour très bientôt. Merci de votre patience.
    </p>
@endsection

