@extends('minigame.base')

@section('title', 'SQL Injection Attack')

@section('pagecontent')

@if ($success ?? '')
    <h1>You did it!</h1>
    
    <form class="sqlInjectionAttack" method="POST" action="/attack/sqlinjection">
        @csrf
        <input type="hidden" name="attID" value="{{$attack->id}}">
        <input type="hidden" name="outcome" value="{{ Session::get('magic_word') }}">
        <div class="form-group row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary">Continue</button>
            </div>
        </div>
    </form>
@else
  <strong>Objective:</strong> {{ $objective ?? '' ?? '' ?? '' }}<br>
  <strong>Difficulty: {{ $attack->getDifficulty() }}/5</strong><br>
@endif

@if (!empty($result))
    <br><strong>Result:</strong><br>
    <div align="center">{!! $result !!}</div>
    <br><br>
@endif

@if (! ($success ?? ''))
<h2>Company Phone Directory:</h2>

<div class= "sqlInjectionContainer">
<form class="sqlInjectionAttack" method="POST" action="/attack/sqlinjection">
    @csrf
    <div class="form-group row">
        <label for="username" class="labelSql">
            username = </label>

<div class="username2Container">
        <input type="text" id="username2" name="username" >
</div><!--end username2Container-->

        <input type="hidden" name="attID" value="{{$attack->id}}">
    </div>
    <div class="form-group row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" class="btn btn-primary">
                Search
            </button>
        </div>
    </div>
</form>
<form method="POST" action="/attack/sqlinjection">
    @csrf
    <input type="hidden" name="attID" value="{{$attack->id}}">
    <input type="hidden" name="outcome" value="resigned">
    <div class="form-group row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" class="btn btn-primary">I give up!</button>
        </div>
    </div>
</form>
</div><!--end sqlInjectionContainer -->
@endif

@endsection
