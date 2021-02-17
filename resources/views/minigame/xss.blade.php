@extends('minigame.base')

@section('title', 'XSS Attack')

@section('pagecontent')
@if (!empty($attack) && $attack->calculated_difficulty > 0) <!-- Until difficulties are made -->
    <h2>Cross Site Script</h2>


<strong>Difficulty: {{ $attack->calculated_difficulty }}</strong>
@endif
<form method="POST" action="/attack/xss">
    @csrf
    <div class="form-group row">
        <label for="url" class="col-md-4 col-form-label text-md-right">
            user ID = </label>
        <input type="text" id="script" name="script" >
        <input type="hidden" name="attID" value="">
    </div>

    <div class="form-group row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" class="btn btn-primary">
                Enter Search
            </button>            
        </div>
    </div>
</form>
@if(!empty($script))
    <p>
    &ltimg src="{{"/images/" . $script}}" /&gt
    </p>
    <img src="<?php echo "/images/" . $script ?>" />
@endif
@endsection
