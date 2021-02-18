@extends('minigame.base')

@section('title', 'XSS Attack')

@section('pagecontent')
<?php $attack->calculated_difficulty = 3 ?>
@if ($attack->calculated_difficulty <= 2)
    <h2>Make a Post</h2>
@elseif ($attack->calculated_difficulty == 3)
    <h2>Enter Image Name</h2>
@endif
<strong>Difficulty: {{ $attack->calculated_difficulty }}</strong>

<form method="POST" action="/attack/xss">
    @csrf
    <div class="form-group row">
        <input type="text" id="script" name="script" >
        <input type="hidden" name="attID" value="{{$attack->id}}">
    </div>

    <div class="form-group row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" class="btn btn-primary">
                Submit
            </button>            
        </div>
    </div>
</form>
@if(!empty($script))
    @if($attack->calculated_difficulty <= 2)
        <h2>Last Post: </h2>
        <p><?php echo $script; ?></p>
        <h2>Code in the post:</h2>
        <p>{{$script}}</p>
    @elseif($attack->calculated_difficulty == 3)
        <h4>Resulting Code:</h4>
        <p>
        &ltimg src="{{"/images/" . $script}}" /&gt
        </p>
        <h4>Resulting Image:</h4>
        <img src="<?php echo "/images/" . $script ?>" />
    @endif
@endif
@endsection
