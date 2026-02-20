<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
 public string $name = '';
 public string $email = '';
 public string $password = '';
 public string $password_confirmation = '';

 /**
 * Handle an incoming registration request.
 */
 public function register(): void
 {
 $validated = $this->validate([
 'name' => ['required', 'string', 'max:255'],
 'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
 'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
 ]);

 $validated['password'] = Hash::make($validated['password']);

 event(new Registered($user = User::create($validated)));

 Auth::login($user);

 // Force full page reload to properly load the dashboard layout
 $this->redirect(route('dashboard', absolute: false), navigate: false);
 }
}; ?>

<div >
 <div >
 <div class="absolute-top-right d-lg-none p-3 p-sm-5">
 <a href="#" class="toggle btn-white btn btn-sm btn-outline-secondary btn-light" data-target="athPromo">
 <em class="icon ni ni-info"></em>
 </a>
 </div>
 <div >
 <div class="brand-logo pb-4 text-center">
 <a href="{{ url('/') }}" class="logo-link">
 <img class="logo-light logo-img logo-img-lg" src="{{ asset('images/logo.png') }}" srcset="{{ asset('images/logo2x.png') }} 2x" alt="logo">
 <img class="logo-dark logo-img logo-img-lg" src="{{ asset('images/logo-dark.png') }}" srcset="{{ asset('images/logo-dark2x.png') }} 2x" alt="logo-dark">
 </a>
 </div>
 <div class="mb-4">
 <div class="mb-4">
 <h4 >Inscription</h4>
 <div >
 <p>Créez votre compte {{ config('app.name') }}</p>
 </div>
 </div>
 </div>

 <form wire:submit="register">
 {{-- Name --}}
 <div class="mb-3">
 <label class="form-label" for="name">Nom complet</label>
 <div >
 <input
 wire:model="name"
 type="text"
 class="form-control form-control-lg @error('name') is-invalid @enderror"
 id="name"
 placeholder="Entrez votre nom complet"
 required
 autofocus
 autocomplete="name"
 >
 @error('name')
 <span class="invalid-feedback" role="alert">
 <strong>{{ $message }}</strong>
 </span>
 @enderror
 </div>
 </div>

 {{-- Email --}}
 <div class="mb-3">
 <label class="form-label" for="email">Email</label>
 <div >
 <input
 wire:model="email"
 type="email"
 class="form-control form-control-lg @error('email') is-invalid @enderror"
 id="email"
 placeholder="Entrez votre adresse email"
 required
 autocomplete="username"
 >
 @error('email')
 <span class="invalid-feedback" role="alert">
 <strong>{{ $message }}</strong>
 </span>
 @enderror
 </div>
 </div>

 {{-- Password --}}
 <div class="mb-3">
 <label class="form-label" for="password">Mot de passe</label>
 <div >
 <a tabindex="-1" href="#" class="form-icon form-icon-right passcode-switch lg" data-target="password">
 <em class="passcode-icon icon-show icon ni ni-eye"></em>
 <em class="passcode-icon icon-hide icon ni ni-eye-off"></em>
 </a>
 <input
 wire:model="password"
 type="password"
 class="form-control form-control-lg @error('password') is-invalid @enderror"
 id="password"
 placeholder="Créez un mot de passe sécurisé"
 required
 autocomplete="new-password"
 >
 @error('password')
 <span class="invalid-feedback" role="alert">
 <strong>{{ $message }}</strong>
 </span>
 @enderror
 </div>
 </div>

 {{-- Password Confirmation --}}
 <div class="mb-3">
 <label class="form-label" for="password_confirmation">Confirmez le mot de passe</label>
 <div >
 <a tabindex="-1" href="#" class="form-icon form-icon-right passcode-switch lg" data-target="password_confirmation">
 <em class="passcode-icon icon-show icon ni ni-eye"></em>
 <em class="passcode-icon icon-hide icon ni ni-eye-off"></em>
 </a>
 <input
 wire:model="password_confirmation"
 type="password"
 class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror"
 id="password_confirmation"
 placeholder="Confirmez votre mot de passe"
 required
 autocomplete="new-password"
 >
 @error('password_confirmation')
 <span class="invalid-feedback" role="alert">
 <strong>{{ $message }}</strong>
 </span>
 @enderror
 </div>
 </div>

 {{-- Terms --}}
 <div class="mb-3">
 <div class="custom-control custom-control-xs custom-checkbox">
 <input type="checkbox" class="custom-control-input" id="terms" required>
 <label class="custom-control-label" for="terms">
 J'accepte les <a tabindex="-1" href="#">Conditions d'utilisation</a> et la <a tabindex="-1" href="#">Politique de confidentialité</a>
 </label>
 </div>
 </div>

 {{-- Submit Button --}}
 <div class="mb-3">
 <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled">
 <span wire:loading.remove>Créer mon compte</span>
 <span wire:loading>
 <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
 Création en cours...
 </span>
 </button>
 </div>
 </form>

 <div class="form-note-s2 text-center pt-4">
 Déjà inscrit? <a href="{{ route('login') }}" wire:navigate>Connectez-vous</a>
 </div>
 </div>

 {{-- Footer --}}
 <div >
 <div class="mt-3">
 <p>&copy; {{ date('Y') }} New Technology Hub Sarl. Tous droits réservés.</p>
 </div>
 </div>
 </div>

 {{-- Right Side - Promo --}}
 <div ></div>
</div>
