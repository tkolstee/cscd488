@extends('redteam.base')

@section('title', 'Red Team Attack History')

@section('pagecontent')
    @if ($previousAttacks->isEmpty())
        <p>You havent done any attacks yet!</p>
    @else
        <table class="table table-bordered table-hover">
                <thead>
                    <th>Attack Type</th>
                    <th>Success</th>
                    <th>Detected</th>
                    <th>Time</th>
                </thead>
            <tbody>
                @foreach ($previousAttacks as $attack)
                    <tr>
                        <td>{{$attack->name}}</td>
                        <td>{{$attack->success ? 'true' : 'false'}}</td>
                        <td>{{($attack->detection_level > 0) ? 'true' : 'false'}}</td>
                        <td>{{$attack->created_at->diffForHumans()}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @include('partials.pagination', ['paginator' => $previousAttacks])
    @endif
@endsection
