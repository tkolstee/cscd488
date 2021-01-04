@extends('blueteam.base')

@section('title', 'News')

@section('pagecontent')
    @if ($detectedAttacks->isEmpty())
        <p>No news yet.</p>
    @else
        @foreach ($detectedAttacks as $attack)
            <p>Team {{App\Models\Team::find($attack->redteam)->name}} attacked Team {{App\Models\Team::find($attack->blueteam)->name}} at {{$attack->updated_at}}</p>
        @endforeach
        @include('partials.pagination', ['paginator' => $detectedAttacks])
    @endif
@endsection
