@extends('minigame.base')

@section('title', 'Malvertise Attack')

@section('pagecontent')
<form method="POST" action="/attack/malvertise">
    @csrf
    <?php function random($attack){
        $randInt = rand(1,4);
        $val = 0;
        if($randInt > $attack->getDifficulty() - 1){
            $val = 1;
        }
        return $val;
    }
    ?>
    <div class="form-group row">
        <input type="hidden" name="attID" value="{{$attack->id}}">
        Select type of Malvertising you want to attempt: <br>
        <?php $val = random($attack); ?>
        <input type="radio" name="result" value="{{ "drivebydownload".$val }}">
        <label>Drive-by-Download</label><br>
        <?php $val = random($attack); ?>
        <input type="radio" name="result" value="{{ "forcedredirect".$val }}">
        <label>Forced Redirect</label><br>
        <?php $val = random($attack); ?>
        <input type="radio" name="result" value="{{ "maliciousjavascript".$val }}">
        <label>Malicious Javascript</label><br>
        <?php $val = random($attack); ?>
        <input type="radio" name="result" value="{{ "executemalware".$val }}">
        <label>Execute Code to install Malware on click</label><br>
        <?php $val = random($attack); ?>
        <input type="radio" name="result" value="{{ "redirect".$val }}">
        <label>Redirect to Malicious Website on click</label><br>
    </div>
    <div class="form-group row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" class="btn btn-primary">
                Submit
            </button>
        </div>
    </div>
</form>
@endsection
