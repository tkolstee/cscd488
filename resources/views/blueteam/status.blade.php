

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
                    <td>
                        @if($attack->detection_level > 1)    
                            {{$bonus->payload_name}}
                        @else
                            ?
                        @endif
                    </td>
                    <td>
                        @if($attack->detection_level > 2)
                            {{App\Models\Team::find($bonus->team_id)->name}}
                        @else
                            ?
                        @endif
                    </td>
                    <td class="bonusDescTd">
                        @if(in_array("RevenueSteal",$bonus->tags))
                            Attacker steals 10% of your revenue made each turn.
                        @endif
                        @if(in_array("RevenueDeduction", $bonus->tags))
                            You lose {{$bonus->percentRevDeducted}}% of revenue made this turn.
                        @endif
                        @if(in_array("ReputationDeduction", $bonus->tags))
                            You lose {{$bonus->percentRepDeducted}}% of reputation made this turn. 
                        @endif
                        @if(in_array("DetectionDeduction", $bonus->tags))
                            You have {{$bonus->percentDetDeducted}}% less chance of detecting this attacker. 
                        @endif
                        @if(in_array("AnalysisDeduction", $bonus->tags))
                            You have {{$bonus->percentAnalDeducted}}% less chance of analyzing this attacker. 
                        @endif
                        @if(in_array("DifficultyDeduction", $bonus->tags))
                            It is {{$bonus->percentDiffDeducted}}% easier for the attacker to be successful against you. 
                        @endif
                        @if(in_array("OneTurnOnly", $bonus->tags))
                            Bonus only lasts until next turn.
                        @elseif(in_array("UntilAnalyzed", $bonus->tags))
                            Bonus lasts until the you analyze the attack.
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
