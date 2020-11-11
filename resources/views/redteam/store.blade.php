@extends('redteam.base')

@section('title', 'Red Team Store')

@section('pagecontent')
    @if(! empty($error))
        <p>{{ $error }}</p>
    @endif
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
                <?php $asset = $assets->where('id','=',$inv->asset_id)->first(); ?>
                <input type="checkbox" name="results[]" id="{{ $asset->name }}" value="{{ $asset->name }}">
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
    <p>This is the red team store.</p>
    @if($assets->isEmpty())
        <p>No items are available for purchase right now.</p>
    @else
    <form method="POST" action="/redteam/buy">
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
