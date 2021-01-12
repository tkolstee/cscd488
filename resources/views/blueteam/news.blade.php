@extends('blueteam.base')

@section('title', 'News')

@section('pagecontent')
    @if ($news->isEmpty())
        <p>No news yet.</p>
    @else
        @foreach ($news as $attack)
            <p>Team {{App\Models\Team::find($attack->redteam)->name}} attacked Team {{App\Models\Team::find($attack->blueteam)->name}} {{$attack->updated_at->diffForHumans()}}</p>
        @endforeach
        @include('partials.pagination', ['paginator' => $news])
    @endif
@endsection
