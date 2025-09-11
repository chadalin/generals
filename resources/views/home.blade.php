@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h4>Welcome to Generals Game!</h4>
                    <p>You are logged in as: <strong>{{ Auth::user()->name }}</strong></p>
                    
                    <div class="mt-4">
                        <a href="{{ route('games.index') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-chess-board"></i> View Games
                        </a>
                        <a href="{{ route('games.create') }}" class="btn btn-success btn-lg ms-2">
                            <i class="fas fa-plus"></i> Create Game
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection