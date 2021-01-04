@extends('blueteam.base')

@section('title', 'Blue Team Inventory')

@section('pagecontent')
    @if($inventory->isEmpty())
        <p>You have no assets.</p>
    @else
        <form method="POST" action="/blueteam/sell">
            @csrf
            @include('partials.inventory_table', ['inventory' => $inventory])
            <button type="submit" class="btn btn-primary">
                Sell
            </button>
        </form>
    @endif
@endsection
