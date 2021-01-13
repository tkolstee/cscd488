
@extends('blueteam.base')

@section('title', 'Blue Team Home')

@section('pagecontent')

    @if (Auth::user()->blueteam == "")
        <a href="/blueteam/create"><button>Create Blue Team</button></a>
        <a href="/blueteam/join"><button>Join Blue Team</button></a>
     @else
        <h3>Team Members:</h3>
        <strong>{{ $leader->username }}</strong><br>
        
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
