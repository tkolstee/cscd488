@extends('minigame.base')

@section('title', 'SQL Injection Attack')

@section('pagecontent')
@if ($attack->difficulty < 5)
<h2>Attempt to find the admin's password using sql injection!</h2>
@elseif ($attack->difficulty == 5)
<h2>Attempt to find the admin's password using sql injection!</h2>
@endif

<strong>Difficulty: {{ $attack->difficulty }}</strong>

@if (!empty($result))
    Result = {{var_dump($result)}}    
@endif

<form method="POST" action="/attack/sqlinjection">
    @csrf
    <div class="form-group row">
        <label for="url" class="col-md-4 col-form-label text-md-right">
            http://{{ $blueteam->name }}.com/user?username=</label>
        <input type="text" id="url" name="url" >
        <label for="pass" class="col-md-4 col-form-label text-md-right">
            Enter admin's password: </label>
        <input type="text" id="pass" name="pass" >
        <input type="hidden" name="attID" value="{{$attack->id}}">
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
