@extends('redteam.base')

@section('title', 'Red Team Home')

@section('pagecontent')
<h3>Select a method of attack against {{ $blueteam->name }}:</h3>
<form method="POST" action="/redteam/performattack">
    @csrf
    @foreach ($inventory ?? [] as $inv)
    <?php $asset = Asset::find($inv->asset_id);?>
    <input type="radio" name="result" id="{{ $asset->name }}" value="{{ $asset->name }}">
    <label for="{{ $asset->name }}">{{ $asset->name }}</label> Quantity: {{ $inv->quantity }}
    <br>
    
    @endforeach

    <div class="form-group row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" class="btn btn-primary">
                Choose Attack
            </button>
        </div>
    </div>
</form>
@endsection
