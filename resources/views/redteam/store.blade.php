@extends('redteam.base')

@section('title', 'Red Team Store')

@section('pagecontent')
    <h4>Red Team Store.</h4>
    <form class="blueStoreForm" method="POST" action="/redteam/filter">
        @csrf
        <p id="tagFilter">Tag Filter: </p>
        <select name="filter">
            <option disabled selected value> -- select an option -- </option>
            @foreach ($tags as $tag)
                <option>{{$tag}}</option>
            @endforeach
        </select>
         <p id="tagFilter">Sort: </p>
        <select name="sort">
            <option disabled selected value> -- select an option -- </option>
            <option value="name">Name</option>
            <option value="purchase_cost">Purchase Cost</option>
        </select>
        <button type="submit" class="btn btn-primary">
            Submit
        </button>
    </form>
    <form  class="blueStoreForm" action="/redteam/store">
        <button class="btn btn-primary">Clear Sort/Filter</button>
    </form>

    @if(count($assets ??[]) == 0)
        <p>No items are available for purchase right now.</p>
    @else
        <form class="storeForm" method="POST" action="/redteam/buy">
            @csrf
            @include('partials.store_assets_table', ['assets' => $assets, 'ownedAssets' => $ownedAssets])
            @include('partials.pagination', ['paginator' => $assets])
            <input type="hidden" name="currentPage" value="{{$assets->currentPage()}}">
            <button type="submit" class="btn btn-primary">
                Purchase
            </button>
        </form>
    @endif
@endsection
