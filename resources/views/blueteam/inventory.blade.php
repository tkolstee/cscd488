@extends('blueteam.base')

@section('title', 'Blue Team Inventory')

@section('pagecontent')
<h4>Blue Team Inventory Store</h4>
    @if($inventory->isEmpty())
        <p>You have no assets.</p>
    @else
    <?php $currentPage = $inventory->currentPage(); ?>
    <div >
        <form class="storeFormInventory" method="POST" action="/blueteam/sell">
            @csrf
            @include('partials.inventory_table', ['inventory' => $inventory])
            <input type="hidden" name="currentPage" value="{{$currentPage}}">
            <button type="submit" class="btn btn-primary" name="sellSubmit">
                Sell
            </button>
        </form>
        @include('partials.pagination', ['paginator' => $inventory])
    </div>
    @endif
@endsection
