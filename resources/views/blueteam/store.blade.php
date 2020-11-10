@extends('blueteam.base')

@section('title', 'Blue Team Store')

@section('pagecontent')
    <p>This is the blue team store.</p>
    @if(! empty($error))
        <p>{{ $error }}</p>
    @endif
    @if($assets->isEmpty())
        <p>No items are available for purchase right now.</p>
    @else
    <form method="POST" action="/blueteam/buy">
        @csrf
        @foreach ($assets as $asset)

        <input type="checkbox" name="results[]" id="{{ $asset->name }}" value="{{ $asset->name }}">
        <label for="{{ $asset->name }}">{{ $asset->name }}  Type: {{ $asset->type }}  Purchase Cost: {{ $asset->purchase_cost }}  Ownership Cost: {{ $asset->ownership_cost }}</label>
        <br>
    
        @endforeach

        <div class="form-group row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary">
                    Purchase
                </button>
            </div>
        </div>
    </form>
    @endif

@endsection
