@extends('blueteam.base')

@section('title', 'Blue Team Store')

@section('pagecontent')
    <h4>Blue Team Store</h4>
    <?php $filter = session('blueFilter'); 
        $sort = session('blueSort'); ?>
    <form class="blueStoreForm" method="POST" action="/blueteam/filter">
        @csrf
        <p id="tagFilter">Tag Filter: </p>
        <select name="filter">
            <option <?php if(empty($filter)){ ?>selected<?php } ?>>No Filter</option>
            @foreach ($tags as $tag)
                <option <?php if($tag == $filter){ echo "selected"; } ?>>{{$tag}}</option>
            @endforeach
        </select>
       <p id="tagFilter">Sort: </p>
        <select name="sort">
            <option value="name" <?php if(empty($sort)){ echo "selected"; } ?>>Name</option>
            <option value="purchase_cost" <?php if("purchase_cost" == $sort){ echo "selected"; } ?>>Purchase Cost</option>
        </select>
        <button type="submit" class="btn btn-primary">
            Submit
        </button>
    </form>
    <form  class="blueStoreForm" action="/blueteam/store">
        <button class="btn btn-primary">Clear Filter</button>
    </form>
    @if(count($assets ?? []) == 0)
        <p>No items are available for purchase right now.</p>
    @else
        <?php $currentPage = $assets->currentPage(); ?>
        <form class="storeForm" method="POST" action="/blueteam/buy">
            @csrf
            @include('partials.store_assets_table', ['assets' => $assets, 'ownedAssets' => $ownedAssets])
            @include('partials.pagination', ['paginator' => $assets])
        <input type="hidden" name="currentPage" value="{{$currentPage}}">
            <button type="submit" class="btn btn-primary">
                Add to Cart
            </button>
        </form>
       
    @endif

@endsection
