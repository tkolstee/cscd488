@extends('redteam.base')

@section('title', 'Red Team Inventory')

@section('pagecontent')
    @if($inventory->isEmpty())
        <p>You have no assets.</p>
    @else
        <form method="POST" action="/redteam/sell">
            @csrf
            @include('partials.inventory_table', ['inventory' => $inventory])
            <button type="submit" class="btn btn-primary" name="sellSubmit">
                Sell
            </button>
        </form>
    @endif
@endsection
