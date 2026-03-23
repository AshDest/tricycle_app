@extends('errors.layout')

@section('title', 'Accès refusé')

@section('content')
    <div class="error-icon" style="background: #fef3c7;">
        <i class="bi bi-shield-lock" style="color: #f59e0b;"></i>
    </div>
    <div class="error-code">403</div>
    <h1 class="error-title">Accès refusé</h1>
    <p class="error-message">
        Vous n'avez pas les permissions nécessaires pour accéder à cette page.
        Contactez votre administrateur si vous pensez qu'il s'agit d'une erreur.
    </p>
@endsection

