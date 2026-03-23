@extends('errors.layout')

@section('title', 'Page introuvable')

@section('content')
    <div class="error-icon" style="background: #eff6ff;">
        <i class="bi bi-search" style="color: #3b82f6;"></i>
    </div>
    <div class="error-code">404</div>
    <h1 class="error-title">Page introuvable</h1>
    <p class="error-message">
        La page que vous recherchez n'existe pas ou a été déplacée.
        Vérifiez l'adresse ou retournez au tableau de bord.
    </p>
@endsection

