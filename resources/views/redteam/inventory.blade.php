@extends('redteam.base')

@section('title', 'Red Team Inventory')

@section('pagecontent')
    @if($inventory->isEmpty())
    <h4>Red Team Inventory Page.</h4>
        <p>You have no assets.</p>
    @else
        <form method="POST" action="/redteam/sell">
            @csrf
            @include('partials.inventory_table', ['inventory' => $inventory])
            @include('partials.pagination', ['paginator' => $inventory])
            <input type="hidden" name="currentPage" value="{{$inventory->currentPage()}}">
            <button type="submit" class="btn btn-primary" name="sellSubmit">
                Sell
            </button>
        </form>
    @endif
@endsection
