@extends('blueteam.base')

@section('title', 'News')

@section('pagecontent')
    @if ($detectedAttacks->isEmpty())
        <p>No news yet.</p>
    @endif
    @foreach ($detectedAttacks as $attack)
        <p>TeamID #{{$attack->redteam}} attacked TeamID #{{$attack->blueteam}} at {{$attack->updated_at}}</p>
    @endforeach
@endsection
