@extends('redteam.base')

@section('title', 'Red Team Store')

@section('pagecontent')
    @if (empty($inventory))
        <form method="POST" action="/redteam/storeinventory">
            @csrf
            <div class="form-group row mb-0">
                <div class="col-md-8 offset-md-4">
                    <button type="submit" class="btn btn-primary">
                        View Your Assets
                    </button>
                </div>
            </div>
        </form>
    @elseif($inventory->isEmpty())
        <p>You have no assets.</p>
    @else
        <form method="POST" action="/redteam/sell">
            @csrf
            @foreach ($inventory ?? [] as $inv)
                <input type="checkbox" name="results[]"id="{{ $inv->asset_name }}" value="{{ $inv->asset_name }}">
                <label for="{{ $inv->asset_name }}">{{ $inv->asset_name }} Quantity: {{$inv->quantity }} </label>
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
    <p>This is the red team store.</p>
    @if(count($assets ??[]) == 0)
        <p>No items are available for purchase right now.</p>
    @else
    <form method="POST" action="/redteam/buy">
        @csrf
        @foreach ($assets as $asset)

        <input type="checkbox" name="results[]" id="{{ $asset->class_name }}" value="{{ $asset->class_name }}">
        <label for="{{ $asset->class_name }}">{{ $asset->name }}  Type: {{ $asset->type }}  
            Purchase Cost: {{ $asset->purchase_cost }}  Ownership Cost: {{ $asset->ownership_cost }}</label>
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
