

@extends('blueteam.base')

@section('title', 'Blue Team Store')

@section('pagecontent')
@if(count($bonuses ?? []) < 1)
        <h4>There are no bonuses targeting you :)</h4>
    @else
        <h4>Bonuses targeting you:</h4>
        <div>
        <table class="table table-bordered">
            <thead>
                <th>Name</th>
                <th>Attacker</th>
                <th class="bonusDescTd">Effect</th>
            </thead>
            <tbody>
                @foreach($bonuses as $bonus)
                    <?php $attack = App\Models\Attack::find($bonus->attack_id); ?>
                    <td>{{$bonus->getPayloadName()}}</td>
                    <td>{{$bonus->getTeamName()}}</td>
                    <td class="bonusDescTd"> {{$bonus->getTargetDescription()}}</td>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
@endsection
