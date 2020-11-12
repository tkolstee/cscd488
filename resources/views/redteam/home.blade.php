@extends('redteam.base')

@section('title', 'Red Team Home')

@section('pagecontent')
    @if (Auth::user()->redteam == "")
        <a href="/redteam/create"><button>Create Red Team</button></a>
     @else
     @if (!empty($attMsg))
    {{ $attMsg }}
     @endif
    <p>This is the red team home page. Much Wow.</p>
    <a href="/redteam/startattack"><button>Start Attack</button></a>
    @endif
@endsection
