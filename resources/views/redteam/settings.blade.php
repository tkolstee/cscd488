@extends('redteam.base')

@section('title', 'Red Team Settings')

@section('pagecontent')
    <h2>Team Settings<br>
    {{ $redteam->name }}</h2>
    <br>
    <h2>User</h2>
    <strong>{{ Auth::user()->username }}</strong>
    <h2>Stats</h2>
    <strong>Balance: </strong>{{ $redteam->balance }}<br>
    <strong>Reputation: </strong>{{ $redteam->reputation }}<br><br>
    @if (!$changeName ?? false)
        <form method="POST" action="/redteam/settings">
        @csrf
            <input type="hidden" name="changeNameBtn" value="1">
            <div class="form-group row mb-0">
                <div class="col-md-8 offset-md-4">
                    <button type="submit" name="changeNameButton" class="btn btn-primary">
                        Change Name
                    </button>
                </div>
            </div>
        </form>
    @elseif ($changeName ?? false)
        <form method="POST" action="/redteam/changename">
        @csrf
        <input id="name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
            <div class="form-group row mb-0">
                <div class="col-md-8 offset-md-4">
                    <button type="submit" name="changeNameSubmit" class="btn btn-primary">
                        Change Name
                    </button>
                </div>
            </div>
        </form>
    @endif
    @if (!$leaveTeam ?? false)

        <form method="POST" action="/redteam/settings">
        @csrf
            <input type="hidden" name="leaveTeamBtn" value="1">
            <div class="form-group row mb-0">
                <div class="col-md-8 offset-md-4">
                    <button type="submit" name="leaveTeamButton" class="btn btn-primary">
                        Leave Team
                    </button>
                </div>
            </div>
        </form>
    @elseif ($leaveTeam ?? false)
        <form method="POST" action="/redteam/leaveteam">
        @csrf
            <input type="radio" name="result" id="leave" value="leave">
            <label for="leave">Leave Team</label>
            <br>
            <input type="radio" name="result" id="stay" value="stay">
            <label for="stay">Stay on Team</label>
            <br>
            <div class="form-group row mb-0">
                <div class="col-md-8 offset-md-4">
                    <button type="submit" name="leaveTeamSubmit" class="btn btn-primary">
                        Leave Team
                    </button>
                </div>
            </div>
        </form>
    @endif
    
@endsection
