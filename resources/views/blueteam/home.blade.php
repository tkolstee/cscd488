
@extends('blueteam.base')

@section('title', 'Blue Team Home')


@section('pagecontent')
<h4 id="h4BlueHomePage"> Blue Team Home Page</h4>

    @if (Auth::user()->blueteam == "")
    <div class="createBlueTeam">
        <a href="/blueteam/create"><button class="btn btn-primary2">Create Blue Team</button></a>
        <a href="/blueteam/join"><button class="btn btn-primary2">Join Blue Team</button></a>
    </div>
     @else
        @if (!empty($actionMsg))
        {{ $actionMsg }}
        @endif
        <h3>Team Members:</h3>
        <p class="userName">{{ $leader->username }}</p<br>
        
        @foreach ($members ?? [] as $member)
        {{ $member->username }}<br>
        @endforeach
        
        @if (($turn ?? 0) == 1)
            <p>Wait until {{ $endTime ?? '' }} for your team's next turn.</p>
        @else
            <p>This is the blue team home page. Much Wow.</p>
            
        @endif

        @if (!$unreadAttacks->isEmpty())
            <h4>Your team was attacked while you were away!</h4>
            @foreach ($unreadAttacks as $attack)
                <p>{{$attack->name}} attack {{$attack->created_at->diffForHumans()}}. 
                    You lost ${{$attack->blue_loss*-1}} and {{$attack->reputation_loss*-1}} reputation
                    <form action="/blueteam/broadcast" method="post">
                        @csrf
                        <input type="hidden" name="attID" value={{$attack->id}}>
                        <input type="submit" name="broadcast" value="Broadcast"/>
                    </form>
                </p>
            @endforeach
            <form  action="/blueteam/clearNotifs" method="post">
                @csrf
                <button>Clear Attack Notifications</button>
            </form>
        @endif
    @endif
@endsection
