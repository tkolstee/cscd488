
@extends('blueteam.base')

@section('title', 'Blue Team Home')


@section('pagecontent')

    @if (Auth::user()->blueteam == "")
    <div class="createBlueTeam">
        <a href="/blueteam/create"><button class="btn btn-primary2">Create Blue Team</button></a>
        <a href="/blueteam/join"><button class="btn btn-primary2">Join Blue Team</button></a>
    </div>
     @else
        <h3>Team Members:</h3>
        <p class="userName">{{ $leader->name }}</p<br>
        
        @foreach ($members ?? [] as $member)
        {{ $member->username }}<br>
        @endforeach
        
        @if (($turn ?? 0) == 1)
            <p>Wait until {{ $endTime ?? '' }} for your team's next turn.</p>
        @else
            <p>This is the blue team home page. Much Wow.</p>
            
        @endif

    @endif
@endsection
