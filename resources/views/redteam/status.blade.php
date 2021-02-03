@extends('redteam.base')

@section('title', 'Red Team Home')

@section('pagecontent')
    @if(count($bonuses ?? []) < 1)
        <h4>You have no bonuses :(</h4>
    @else
        <h4>Your Bonuses:</h4>
        <div>
        <table class="table table-bordered">
            <thead>
                <th>Name</th>
                <th>Target</th>
                <th class="bonusDescTd">Effect</th>
            </thead>
            <tbody>
                @foreach($bonuses as $bonus)
                <tr>
                    <td>{{$bonus->payload_name}}</td>
                    <td>{{App\Models\Team::find($bonus->target_id)->name}}</td>
                    <td class="bonusDescTd">{{$bonus->getTeamDescription()}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @include('partials.pagination', ['paginator' => $bonuses])
    @endif
@endsection
