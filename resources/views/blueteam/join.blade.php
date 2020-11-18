@extends('blueteam.base')

@section('title', 'Blue Team Create')

@section('pagecontent')

    <h2>Join A Team</h2>
    @if ($blueteams->isEmpty())
        No teams to join.<br>
    @else
        <form method="POST" action="/blueteam/join">
        @csrf
            @foreach ($blueteams as $blueteam)
            <input type="radio" name="result" id="{{ $blueteam->name }}" value="{{ $blueteam->name }}">
            <label for="{{ $blueteam->name }}">{{ $blueteam->name }}</label>
            <br>
            @endforeach
            <div class="form-group row mb-0">
                <div class="col-md-8 offset-md-4">
                    <button type="submit" class="btn btn-primary">
                        Join Team
                    </button>
                </div>
            </div>
        </form>
    @endif
@endsection
