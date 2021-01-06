@extends('blueteam.base')

@section('title', 'Blue Team Store')

@section('pagecontent')
    @if (empty($inventory))
        <form class="storeForm" method="POST" action="/blueteam/storeinventory">
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
        <form class="storeForm" method="POST" action="/blueteam/sell">
            @csrf
            @foreach ($inventory ?? [] as $inv)
                <?php $asset = $assets->find($inv->asset_id); ?>
                <input type="checkbox" name="results[]" id="{{ $asset->id }}" value="{{ $asset->name }}">
                <label for="{{ $asset->name }}">{{ $asset->name }} Quantity: {{$inv->quantity }} 
                    Type: {{ $asset->type }} Purchase Cost: {{ $asset->purchase_cost }}  
                    Ownership Cost: {{ $asset->ownership_cost }}</label>
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
    <p>This is the blue team store.</p>
    @if($assets->isEmpty())
        <p>No items are available for purchase right now.</p>
    @else
    <form class="storeForm" method="POST" action="/blueteam/buy">
        @csrf
        @foreach ($assets as $asset)

        <input type="checkbox" name="results[]" id="{{ $asset->id }}" value="{{ $asset->name }}">
        <label for="{{ $asset->name }}">{{ $asset->name }}  Type: {{ $asset->type }}  Purchase Cost: {{ $asset->purchase_cost }}  Ownership Cost: {{ $asset->ownership_cost }}</label>
        <br>
    
        @endforeach

        <div class="form-group row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary">
                    Add to cart
                </button>
            </div>
        </div>
    </form>
    @endif

@endsection
