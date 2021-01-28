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
                    <td>{{$bonus->payload_name}}</td>
                    <td>{{App\Models\Team::find($bonus->target_id)->name}}</td>
                    <td class="bonusDescTd">
                        @if(in_array("RevenueSteal",$bonus->tags))
                            Steals 10% of target's revenue made each turn.
                        @endif
                        @if(in_array("RevenueDeduction", $bonus->tags))
                            Target loses {{$bonus->percentRevDeducted}}% of revenue made this turn.
                        @endif
                        @if(in_array("ReputationDeduction", $bonus->tags))
                            Target loses {{$bonus->percentRepDeducted}}% of reputation made this turn. 
                        @endif
                        @if(in_array("DetectionDeduction", $bonus->tags))
                            You have {{$bonus->percentDetDeducted}}% less chance of being detected by target. 
                        @endif
                        @if(in_array("AnalysisDeduction", $bonus->tags))
                            You have {{$bonus->percentAnalDeducted}}% less chance of being analyzed by target. 
                        @endif
                        @if(in_array("DifficultyDeduction", $bonus->tags))
                            It is {{$bonus->percentDiffDeducted}}% easier to be successful attacking the target. 
                        @endif
                        @if(in_array("OneTurnOnly", $bonus->tags))
                            Bonus only lasts until next turn.
                        @elseif(in_array("UntilAnalyzed", $bonus->tags))
                            Bonus lasts until the target analyzes the attack.
                        @else
                            Decrements by 5% each turn.
                        @endif
                    </td>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
@endsection
