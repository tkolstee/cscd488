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
                        <td>
                            @if ($attack->detection_level < 2)
                                <form action="/blueteam/analyzeAttack" method="post">
                                    @csrf
                                    <input type="hidden" name="attID" value={{$attack->id }}>
                                    <input type="submit" name="analyze" value="Pay 500 To Analyze"/>
                                </form>
                            @else
                                {{$attack->name}}
                            @endif
                        </td>
                        <td>{{App\Models\Team::find($attack->redteam)->name}}</td>
                        <td>{{$attack->success ? 'true' : 'false'}}</td>
                        <td>{{$attack->created_at->diffForHumans()}}</td>
                        @if (!$attack->isNews && $attack->created_at->diffInDays() <= 3)
                            <td>
                                <form action="/blueteam/broadcast" method="post">
                                    @csrf
                                    <input type="hidden" name="attID" value={{$attack->id }}>
                                    <input type="submit" name="broadcast" value="Broadcast"/>
                                </form>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
        @include('partials.pagination', ['paginator' => $previousAttacks])
    @endif
@endsection
