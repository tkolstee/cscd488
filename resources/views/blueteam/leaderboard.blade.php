@extends('blueteam.base')

@section('title', 'Leaderboard')

@section('pagecontent')
<div class="leaderBoardContainer">
    <table class="table table-bordered">
        <thead >
            <th>Rank</th>
            <th>Name</th>
            <th>Reputation</th>
        </thead>
        <tbody>
            @foreach($teams as $team)
                <tr>
                    <td>{{($loop->index + 1) + ($teams->perPage() * ($teams->currentPage() - 1)) }}</td>
                    <td>{{$team->name}}</td>
                    <td>{{$team->reputation}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@include('partials.pagination', ['paginator' => $teams])
@endsection
