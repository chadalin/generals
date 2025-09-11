@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Active Games</h1>
    
    <div class="row mb-3">
        <div class="col">
            <a href="{{ route('games.create') }}" class="btn btn-primary">Create New Game</a>
        </div>
    </div>

    <div class="row">
        @foreach($games as $game)
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ $game->name }}</h5>
                    <p class="card-text">
                        Players: {{ $game->current_players }}/{{ $game->max_players }}<br>
                        Status: {{ ucfirst($game->status) }}<br>
                        Year: {{ $game->year }}
                    </p>
                    <a href="{{ route('games.show', $game->id) }}" class="btn btn-primary">Join Game</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{ $games->links() }}
</div>
@endsection