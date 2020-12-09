@extends('redteam.base')

@section('title', 'Red Team Inventory')

@section('pagecontent')
    @if($inventory->isEmpty())
        <p>You have no assets.</p>
    @else
        <form method="POST" action="/redteam/sell">
            @csrf
            @foreach ($inventory ?? [] as $inv)
                <input type="checkbox" name="results[]"id="{{ $inv->asset_name }}" value="{{ $inv->asset_name }}">
                <label for="{{ $inv->asset_name }}">{{ App\Models\Asset::get($inv->asset_name)->name }} Quantity: {{$inv->quantity }} </label>
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
