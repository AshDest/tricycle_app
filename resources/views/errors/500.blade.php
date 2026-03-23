@extends('errors.layout')

@section('title', 'Erreur 500')

@section('content')
    <div class="error-icon" style="background: #fef2f2;">
        <i class="bi bi-exclamation-triangle" style="color: #ef4444;"></i>
    </div>
    <div class="error-code">500</div>
    <h1 class="error-title">Erreur interne du serveur</h1>
    <p class="error-message">
        Une erreur inattendue s'est produite. Notre équipe technique a été informée et travaille à résoudre le problème.
        Veuillez réessayer dans quelques instants.
    </p>
@endsection
