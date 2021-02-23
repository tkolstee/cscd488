@extends('blueteam.base')

@section('title', 'Blue Team Market')

@section('pagecontent')
<a href="/blueteam/market"><button type="submit" class="btn btn-primary2">Return to Market</button></a>
@include('partials.completed_trades', ['boughtTrades' => $boughtTrades, 'soldTrades' => $soldTrades])
@endsection
