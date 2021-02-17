@extends('minigame.base')

@section('title', 'SQL Injection Attack')

@section('pagecontent')
@if ($attack->calculated_success_chance <= 1)
    <h2>Attempt to cause a SQL error!</h2>
@elseif ($attack->calculated_success_chance > 1)
    <h2>Attempt to find the admins password using sql injection!</h2>
@endif

<strong>Difficulty: {{ $attack->calculated_success_chance }}</strong>

@if (!empty($result))
    Result = {{var_dump($result)}}    
@endif

<form method="POST" action="/attack/sqlinjection">
    @csrf
    <div class="form-group row">
        <label for="url" class="col-md-4 col-form-label text-md-right">
            user ID = </label>
        <input type="text" id="url" name="url" >
        <input type="hidden" name="attID" value="{{$attack->id}}">
    </div>

    <div class="form-group row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" class="btn btn-primary">
                Enter Search
            </button>            
        </div>
    </div>
</form>

@if($attack->calculated_success_chance > 1)
    <form method="POST" action="/attack/sqlinjectioncheck">
        @csrf
        <div class="form-group row">
            <label for="pass" class="col-md-4 col-form-label text-md-right">
                Enter admins password: </label>
            <input type="text" id="pass" name="pass" >
            <input type="hidden" name="attID" value="{{$attack->id}}">
        </div>

        <div class="form-group row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary">
                    Submit Answer
                </button>            
            </div>
        </div>
    </form>
@endif

@endsection
