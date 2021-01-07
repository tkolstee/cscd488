@extends('redteam.base')

@section('title', 'Red Team Store')

@section('pagecontent')
    <p>This is the red team store.</p>
    <form method="POST" action="/redteam/filter">
        @csrf
        Tag Filter: 
        <select name="filter" onchange="this.form.submit();">
            <option disabled selected value> -- select an option -- </option>
            @foreach ($tags as $tag)
                <option>{{$tag}}</option>
            @endforeach
        </select>
    </form>
    <form  action="/redteam/store">
        <button>Clear Filter</button>
    </form>

    @if(count($assets ??[]) == 0)
        <p>No items are available for purchase right now.</p>
    @else
        <form method="POST" action="/redteam/buy">
            @csrf
            @include('partials.store_assets_table', ['assets' => $assets])
            <button type="submit" class="btn btn-primary">
                Purchase
            </button>
        </form>
    @endif
@endsection
