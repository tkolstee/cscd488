@extends('blueteam.base')

@section('title', 'Blue Team Pick Target')

@section('pagecontent')
@if(!empty($redteams) && !empty($targeted))
    <form class="blueTeamJoinForm" method="POST" action="/blueteam/picktarget">
        @csrf
            <?php $count = 0; ?>
        @foreach($targeted as $inv)
            <table id="joinTable">
            <?php $count++; ?>
            <h4>Pick Target for {{App\Models\Asset::get($inv->asset_name)->name }}:</h4>
            @foreach ( ($redteams ?? []) as $redteam)
                <tr >
                <input type="hidden" name="{{"name".$count }}" value="{{ $inv->asset_name }}">
                <td id="joinTdButton"><input type="radio" name="{{"result".$count }}" id="{{ $redteam->name }}" value="{{ $redteam->name }}"></td>
                <td class="joinTd"><label class="chooseTeamRadioButtons for="{{ $redteam->name }}">{{ $redteam->name }}</label></td>
                </tr>
            @endforeach
            </table>
        @endforeach
            <input type="hidden" name="invCount" value="{{ $count }}">
            <input type="hidden" name="currentPage" value="{{ $currentPage }}">
            <input type="hidden" name="endTurn" value="{{$endTurn}}">
            <div class="form-group row mb-0">
                <div class="col-md-8 offset-md-4">
                    <button type="submit" class="btn btn-primary">
                        Select Target
                    </button>
                </div>
            </div>
        </form>
@else
    <h4>There are no teams to target right now.</h4>
    <form class="blueTeamJoinForm" method="POST" action="/blueteam/inventory">
        @csrf
    <button type="submit" class="btn btn-primaryInventory" 
                        name="submit">
                        Return to Inventory</button>
    </form>
@endif
@endsection
