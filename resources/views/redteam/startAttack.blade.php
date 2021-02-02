@extends('redteam.base')

@section('title', 'Red Team Home')

@section('pagecontent')
<h4>Select a blue team to attack:</h4>
<form method="POST" action="/redteam/chooseattack">
    @csrf
    @foreach ($targets as $target)
    <input type="radio" name="result" id="{{ $target->name }}" value="{{ $target->name }}">
    <label class="chooseTeamRadioButtons" for="{{ $target->name }}">{{ $target->name }}</label>
    <br>
    @endforeach
    @include('partials.pagination', ['paginator' => $targets])
    <div class="form-group row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" class="btn btn-primary">
                Attack
            </button>
        </div>
    </div>
</form>
@endsection
