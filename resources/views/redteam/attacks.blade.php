@extends('redteam.base')

@section('title', 'Red Team Home')

@section('pagecontent')
    @if ($previousAttacks->isEmpty())
        <p>You haven't done any attacks yet!</p>
    @else
        @foreach ($previousAttacks as $attack)
            <p>Type: {{$attack->name}} Success: {{$attack->success}}  Detected: {{$attack->detected}}  Time: {{$attack->created_at}}</p>
        @endforeach
    @endif
@endsection
