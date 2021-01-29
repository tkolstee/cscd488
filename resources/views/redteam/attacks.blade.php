@extends('redteam.base')

@section('title', 'Red Team Attack History')

@section('pagecontent')
    @if ($previousAttacks->isEmpty())
    <h4>Red Team Attack Page.</h4>
        <p>You havent done any attacks yet!</p>
    @else
    <h4>Red Team Attack Page.</h4>
       
        <table class="table table-bordered">
                <thead>
                    <th>Attack Type</th>
                    <th>Success</th>
                    <th>Detected</th>
                    <th>Analyzed</th>
                    <th>Attributed</th>
                    <th>Time</th>
                </thead>
            <tbody>
                @foreach ($previousAttacks as $attack)
                    <tr>
                        <td>{{$attack->name}}</td>
                        <td>{{$attack->success ? 'true' : 'false'}}</td>
                        <td>{{($attack->detection_level > 0) ? 'True' : 'False'}}</td>
                        <td>{{($attack->detection_level > 1) ? 'True' : 'False'}}</td>
                        <td>{{($attack->detection_level > 2) ? 'True' : 'False'}}</td>
                        <td>{{$attack->created_at->diffForHumans()}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @include('partials.pagination', ['paginator' => $previousAttacks])
    @endif
@endsection
