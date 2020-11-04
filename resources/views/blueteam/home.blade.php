@extends('blueteam.base')

@section('title', 'Blue Team Home')

@section('pagecontent')

    @if (Auth::user()->blueteam == "")
        <a href="/blueteam/create"><button>Create Blue Team</button></a>
        <a href="/blueteam/index"><button>Join Blue Team</button></a>
     @else
    <p>This is the blue team home page. Much Wow.</p>

    @endif
@endsection
