@extends('redteam.base')

@section('title', 'Red Team Market')

@section('pagecontent')
<h4>Create Trade</h4>
@if(empty($inventories))
    <h4>You have no assets to trade :(</h4>
@else
    <h4>Select Asset to Trade</h4>
    <form method="POST" action="/redteam/createtrade">
    @csrf
        @include('partials.create_trade_form', ['inventories' => $inventories])
    </form>
@endif
@endsection
