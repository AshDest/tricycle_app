@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5>Notifications</h5>
                </div>
                <div class="card-body">
                    @forelse(auth()->user()->notifications ?? [] as $notification)
                        <div class="alert alert-{{ $notification->data['alert_type'] ?? 'info' }} alert-icon alert-icon-top alert-dismissible" role="alert">
                            <div class="alert-icon-bg">
                                <em class="icon ni ni-{{ $notification->data['icon'] ?? 'bell' }}"></em>
                            </div>
                            <div class="alert-content">
                                <h6 class="alert-title">{{ $notification->data['title'] ?? 'Notification' }}</h6>
                                <p class="alert-text">{{ $notification->data['message'] ?? '' }}</p>
                                <p class="alert-text text-sm text-soft">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                            <button class="btn-close" data-dismiss="alert"></button>
                        </div>
                    @empty
                        <div class="alert alert-icon alert-info" role="alert">
                            <div class="alert-icon-bg">
                                <em class="icon ni ni-info-circle"></em>
                            </div>
                            <div class="alert-content">
                                <p>Aucune notification</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
