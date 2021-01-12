@extends('blueteam.base')

@section('title', 'Blue Team Attack History')

@section('pagecontent')
    @if ($previousAttacks->isEmpty())
        <p>You havent experienced any attacks yet!</p>
    @else
        <table class="table table-bordered table-hover">
                <thead>
                    <th>Attack Type</th>
                    <th>Attacker</th>
                    <th>Success</th>
                    <th>Time</th>
                </thead>
            <tbody>
                @foreach ($previousAttacks as $attack)
                    <tr>
                        <td>{{$attack->name}}</td>
                        <td>{{App\Models\Team::find($attack->redteam)->name}}</td>
                        <td>{{$attack->success ? 'true' : 'false'}}</td>
                        <td>{{$attack->created_at->diffForHumans()}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @include('partials.pagination', ['paginator' => $previousAttacks])
    @endif
@endsection
