@extends('blueteam.base')

@section('title', 'Blue Team Create')

@section('pagecontent')

    <h4>Join A Team</h4>
    @if ($blueteams->isEmpty())
        No teams to join.<br>
    @else
        <form class="blueTeamJoinForm" method="POST" action="/blueteam/join">
        @csrf
            @foreach ($blueteams as $blueteam)
            <input type="radio" name="result" id="{{ $blueteam->name }}" value="{{ $blueteam->name }}">
            <label class="chooseTeamRadioButtons" for="{{ $blueteam->name }}">{{ $blueteam->name }}</label>
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
