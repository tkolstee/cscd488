@extends('blueteam.base')

@section('title', 'Blue Team Inventory')

@section('pagecontent')
<h4>Blue Team Inventory Store</h4>
    @if($inventory->isEmpty())

        <p>You have no assets.</p>
    @else
        <form class="storeFormInventory" method="POST" action="/blueteam/sell">
            @csrf
            @include('partials.inventory_table', ['inventory' => $inventory])
            <button type="submit" class="btn btn-primary" name="sellSubmit">
                Sell
            </button>
        </form>
    @endif
@endsection
