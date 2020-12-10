@extends('redteam.base')

@section('title', 'Red Team Store')

@section('pagecontent')
    <p>This is the red team store.</p>
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
