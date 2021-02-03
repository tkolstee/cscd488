@extends('redteam.base')

@section('title', 'Red Team Store')

@section('pagecontent')
    <h4>Red Team Store.</h4>
    <?php $filter = session('redFilter');
        $sort = session('redSort'); ?>
    <form class="blueStoreForm" method="POST" action="/redteam/filter">
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
    <form  class="blueStoreForm" action="/redteam/store">
        <button class="btn btn-primary">Clear Sort/Filter</button>
    </form>

    @if(count($assets ??[]) == 0)
        <p>No items are available for purchase right now.</p>
    @else
        <form class="storeForm" method="POST" action="/redteam/buy">
            @csrf
            @include('partials.store_assets_table', ['assets' => $assets, 'ownedAssets' => $ownedAssets])
            <input type="hidden" name="currentPage" value="{{$assets->currentPage()}}">
            <button type="submit" class="btn btn-primary">
                Purchase
            </button>
        </form>
        @include('partials.pagination', ['paginator' => $assets])
    @endif
@endsection
