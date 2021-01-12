@extends('blueteam.base')

@section('title', 'Blue Team Store')

@section('pagecontent')
    <h4>Blue Team Store</h4>
    <form method="POST" action="/blueteam/filter">
        @csrf
        Tag Filter: 
        <select name="filter" onchange="this.form.submit();">
            <option disabled selected value> -- select an option -- </option>
            @foreach ($tags as $tag)
                <option>{{$tag}}</option>
            @endforeach
        </select>
    </form>
    <form  action="/blueteam/store">
        <button>Clear Filter</button>
    </form>
    
    @if(count($assets ?? []) == 0)
        <p>No items are available for purchase right now.</p>
    @else
        <form class="storeForm" method="POST" action="/blueteam/buy">
            @csrf
            @include('partials.store_assets_table', ['assets' => $assets, 'ownedAssets' => $ownedAssets])
            <button type="submit" class="btn btn-primary">
                Add to Cart
            </button>
        </form>
    @endif

@endsection
