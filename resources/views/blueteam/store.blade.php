@extends('blueteam.base')

@section('title', 'Blue Team Store')

@section('pagecontent')
    <p>This is the blue team store.</p>
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
        <form method="POST" action="/blueteam/buy">
            @csrf
            @include('partials.store_assets_table', ['assets' => $assets])
            <button type="submit" class="btn btn-primary">
                Purchase
            </button>
        </form>
    @endif

@endsection
