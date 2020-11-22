@extends('redteam.base')

@section('title', 'Red Team Home')

@section('pagecontent')
<h3>Performing {{ $attack->name }}
against {{ $blueteam->name }}:</h3>
<form method="POST" action="/redteam/attackhandler">
    @csrf
    <input type="radio" name="result" id="success" value="success">
        <label for="success">Success</label>
    <input type="radio" name="result" id="failure" value="failure">
        <label for="failure">Failure</label>
    <input type="hidden" name="blueteam" value="{{ $blueteam->name }}">
    <input type="hidden" name="attack" value="{{ $attack->name }}">
    <div class="form-group row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" class="btn btn-primary">
                Submit
            </button>
        </div>
    </div>
</form>
@endsection
