@extends('minigame.base')

@section('title', 'Syn Flood Attack')

@section('pagecontent')
    
<h2>Select a rate at which to send SYN packets</h2>
<form method="POST" action="/attack/synflood">
    @csrf

    <div class="form-group row">
        <input type="radio" name="rate" id="20" value="{{  }}">
        <input type="hidden" name="attackName" value="{{ $attack->class_name }}">
        <input type="hidden" name="red" value="{{ $redteam->id }}">
        <input type="hidden" name="blue" value="{{ $blueteam->id }}">
    </div>

    <div class="form-group row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" class="btn btn-primary">
                Enter URL
            </button>

            
        </div>
    </div>
</form>

@endsection