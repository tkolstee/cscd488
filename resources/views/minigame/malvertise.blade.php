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
    <div class="form-group row2">
        <input type="hidden" name="attID" value="{{$attack->id}}">
        <p class="malP">
        Select type of Malvertising you want to attempt:</p> <br>
        <?php $val = random($attack); ?>
        <input type="radio" name="result" value="{{ "drivebydownload".$val }}">
        <label class="malLabel">Drive-by-Download</label><br>
        <?php $val = random($attack); ?>
        <input type="radio" name="result" value="{{ "forcedredirect".$val }}">
        <label class="malLabel">Forced Redirect</label><br>
        <?php $val = random($attack); ?>
        <input type="radio" name="result" value="{{ "maliciousjavascript".$val }}">
        <label class="malLabel">Malicious Javascript</label><br>
        <?php $val = random($attack); ?>
        <input type="radio" name="result" value="{{ "executemalware".$val }}">
        <label class="malLabel">Execute Code to install Malware on click</label><br>
        <?php $val = random($attack); ?>
        <input type="radio" name="result" value="{{ "redirect".$val }}">
        <label class="malLabel">Redirect to Malicious Website on click</label><br>
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
