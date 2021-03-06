@extends('blueteam.base')

@section('title', 'Blue Team Settings')

@section('pagecontent')
    <h3>Blue Team Settings<br></h3>
        <p class="userName">{{ $blueteam->name }}</p>
    <h3>Team Members:</h3>
    <p class="userName">Leader: {{ $leader->username }}</p><br>
    @foreach ($members ?? [] as $member)
        {{ $member->username }}<br>
    @endforeach
    <h3>Stats</h3>
    <strong>Balance: </strong>{{ $blueteam->balance }}<br>
    <strong>Reputation: </strong>{{ $blueteam->reputation }}<br><br>
    @if (Auth::user()->leader == 1)
        @if (!$changeName ?? false)
            <form method="POST" action="/blueteam/settings">
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
            <form method="POST" action="/blueteam/changename">
            @csrf
            <input id="name2" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                <div class="form-group row mb-0">
                    <div class="col-md-8 offset-md-4">
                        <button type="submit" name="changeNameSubmit" class="btn btn-primary">
                            Change Name
                        </button>
                    </div>
                </div>
            </form>
        @endif
        @if(count($members) > 0)
            @if (!($changeLeader ?? false))
                <form method="POST" action="/blueteam/settings">
                @csrf
                    <input type="hidden" name="changeLeaderBtn" value="1">
                    <div class="form-group row mb-0">
                        <div class="col-md-8 offset-md-4">
                            <button type="submit" name="changeLeaderButton" class="btn btn-primary">
                                Change Leader
                            </button>
                        </div>
                    </div>
                </form>
            @elseif($changeLeader ?? false)
            <form method="POST" action="/blueteam/changeleader">
            @csrf
                @foreach ($members as $member)
                <input type="radio" name="result" id="{{ $leader->username }}" value="{{ $member->username }}">
                <label for="{{ $member->username }}">{{ $member->username }}</label>
                <br>
                @endforeach
                <div class="form-group row mb-0">
                    <div class="col-md-8 offset-md-4">
                        <button type="submit" class="btn btn-primary">
                            Change Leader
                        </button>
                    </div>
                </div>
            </form>
            @endif
        @endif
    @endif
    @if (!$leaveTeam ?? false)

        <form class="blueSettingForm"method="POST" action="/blueteam/settings">
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
        <form method="POST" action="/blueteam/leaveteam">
        @csrf
            <input type="radio" name="result" id="leave" value="leave">
            <label class="leaveAndStayOnTeam" for="leave">Leave Team</label>
            <br>
            <input type="radio" name="result" id="stay" value="stay">
            <label class="leaveAndStayOnTeam" for="stay">Stay on Team</label>
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
