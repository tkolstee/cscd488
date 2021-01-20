@extends('blueteam.base')

@section('title', 'Blue Team Create')

@section('pagecontent')

    <h4>Join A Team</h4>
    @if (count($blueteams ?? []) == 0)
        No teams to join.<br>
    @else
        <form class="blueTeamJoinForm" method="POST" action="/blueteam/join">
        @csrf
        <table id="joinTable">
            @foreach ($blueteams as $blueteam)
            <tr >
            <td id="joinTdButton"><input type="radio" name="result" id="{{ $blueteam->name }}" value="{{ $blueteam->name }}"></td>
            <td class="joinTd"><label class="chooseTeamRadioButtons for="{{ $blueteam->name }}">{{ $blueteam->name }}</label></td>
            @if(($viewMembers ?? null) != $blueteam->name)
                <td><button type="submit" formaction="/blueteam/joinmembers" 
                                class="btn btn-primary" 
                                name="submit" value="{{$blueteam->name }}">
                                View Members</button><td>
            @else
                <td class="userNameBlue">Leader: {{$viewTeamLeader->username }}</td>
                @if(count($viewTeamMembers) > 0)
                    </tr><tr><td></td><td class="userNameBlue"> Members: </td>
                    @foreach($viewTeamMembers as $member)
                        <td class="userNameBlue">{{$member->username }}</td>
                    @endforeach
                @endif
            @endif
            </tr>
            @endforeach
        </table>
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
