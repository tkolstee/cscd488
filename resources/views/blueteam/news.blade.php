@extends('blueteam.base')

@section('title', 'News')

@section('pagecontent')
    @if ($news->isEmpty())
        <h4>No news yet.</h4>
    @else
    <table class="table table-bordered ">
                <thead>
                    <th>Victim</th>
                    <th>Attack Type</th>
                    <th>Attacker</th>
                    <th>Success</th>
                    <th>Time</th>
                </thead>
            <tbody>
                @foreach ($news as $attack)
                    <tr>
                        <td>{{App\Models\Team::find($attack->blueteam)->name}}</td>
                        <td>{{$attack->getName()}}</td>
                        <td>{{$attack->getAttackerName()}}</td>
                        <td>{{$attack->success ? 'True' : 'False'}}</td>
                        <td>{{$attack->created_at->diffForHumans()}}</td>
                        @if (attack_broadcastable($attack))
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
        @include('partials.pagination', ['paginator' => $news])
    @endif
@endsection
