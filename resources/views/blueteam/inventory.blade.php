@extends('blueteam.base')

@section('title', 'Blue Team Inventory')

@section('pagecontent')
    @if($inventory->isEmpty())
        <p>You have no assets.</p>
    @else
        <form method="POST" action="/blueteam/sell">
            @csrf
            @foreach ($inventory ?? [] as $inv)
                <input type="checkbox" name="results[]" id="{{ $inv->id }}" value="{{ $inv->asset_name }}">
                <label for="{{ $inv->id }}">{{ App\Models\Asset::get($inv->asset_name)->name }} Quantity: {{$inv->quantity }} </label>
                <br>
            @endforeach

            <div class="form-group row mb-0">
                <div class="col-md-8 offset-md-4">
                    <button type="submit" class="btn btn-primary">
                        Sell
                    </button>
                </div>
            </div>
        </form>
    @endif
@endsection
