@extends('errors.layout')

@section('title', 'Non autorisé')

@section('content')
    <div class="error-icon" style="background: #fef3c7;">
        <i class="bi bi-person-lock" style="color: #f59e0b;"></i>
    </div>
    <div class="error-code">401</div>
    <h1 class="error-title">Non autorisé</h1>
    <p class="error-message">
        Vous devez être connecté pour accéder à cette page.
        Veuillez vous identifier pour continuer.
    </p>
@endsection

