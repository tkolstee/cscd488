@extends('redteam.base')

@section('title', 'Red Team Home')

@section('pagecontent')
    @if ($previousAttacks->isEmpty())
        <p>You haven't done any attacks yet!</p>
    @else
        @foreach ($previousAttacks as $attack)
            <p>Type: {{$attack->name}} Success: {{$attack->success ? 'true' : 'false'}}  Detected: {{$attack->detected ? 'true' : 'false'}}  Time: {{$attack->created_at}}</p>
        @endforeach

        @include('partials.pagination', ['paginator' => $previousAttacks])
    @endif
@endsection
