@extends('blueteam.base')

@section('title', 'Blue Team Store')

@section('pagecontent')
    @if (empty($inventory))
        <form method="POST" action="/blueteam/storeinventory">
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
    <p>This is the blue team store.</p>
    @if(count($assets ?? []) == 0)
        <p>No items are available for purchase right now.</p>
    @else
    <form method="POST" action="/blueteam/buy">
        @csrf
        @foreach ($assets as $asset)

        <input type="checkbox" name="results[]" id="{{ $asset->class_name }}" value="{{ $asset->class_name }}">
        <label for="{{ $asset->class_name }}">{{ $asset->name }}
            Purchase Cost: {{ $asset->purchase_cost }}  Ownership 
                @if ($asset->ownership_cost >= 0)
                    Cost: {{ $asset->ownership_cost }}
                @else
                    Profit: {{ (-1 * $asset->ownership_cost )}}
                @endif
                </label>
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
