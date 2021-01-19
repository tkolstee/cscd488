@extends('blueteam.base')

@section('title', 'Blue Team Pick Target')

@section('pagecontent')

<form class="blueTeamJoinForm" method="POST" action="/blueteam/picktarget">
        @csrf
        <?php $count = 0;?>
        <input type="hidden" name="invs" value="{{$targeted}}">
        @foreach( ($targeted ?? []) as $inv)
            <table id="joinTable">
            <?php $asset = App\Models\Asset::get($inv->asset_name); 
            $count++;?>
            <h4>Pick Target for {{$asset->name }}:</h4>
            @foreach ( ($redteams ?? []) as $redteam)
                <tr >
                <td id="joinTdButton"><input type="radio" name="{{"result".$count }}" id="{{ $redteam->name }}" value="{{ $redteam->name }}"></td>
                <td class="joinTd"><label class="chooseTeamRadioButtons for="{{ $redteam->name }}">{{ $redteam->name }}</label></td>
                </tr>
            @endforeach
            </table>
        @endforeach
            <div class="form-group row mb-0">
                <div class="col-md-8 offset-md-4">
                    <button type="submit" class="btn btn-primary">
                        Select Target
                    </button>
                </div>
            </div>
        </form>
@endsection
