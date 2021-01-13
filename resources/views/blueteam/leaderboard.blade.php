@extends('blueteam.base')

@section('title', 'Leaderboard')

@section('pagecontent')
    <table class="table table-bordered table-hover">
        <thead>
            <th>Rank</th>
            <th>Name</th>
            <th>Reputation</th>
        </thead>
        <tbody>
            @foreach($teams as $team)
                <tr>
                    <td>{{$loop->index + 1}}</td>
                    <td>{{$team->name}}</td>
                    <td>{{$team->reputation}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
