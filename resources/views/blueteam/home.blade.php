
@extends('blueteam.base')

@section('title', 'Blue Team Home')

@section('pagecontent')

    @if (Auth::user()->blueteam == "")
        <a href="/blueteam/create"><button>Create Blue Team</button></a>
        <a href="/blueteam/join"><button>Join Blue Team</button></a>
     @else
     <h3>Team Members:</h3>
     <strong>{{ $leader->name }}</strong><br>
     
    @foreach ($members ?? [] as $member)
    {{ $member->name }}<br>
    @endforeach
     
    <p>This is the blue team home page. Much Wow.</p>
    

    @endif
@endsection
