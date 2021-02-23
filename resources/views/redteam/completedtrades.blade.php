@extends('redteam.base')

@section('title', 'Red Team Market')

@section('pagecontent')
<a href="/redteam/market"><button type="submit" class="btn btn-primary2">Return to Market</button></a>
@include('partials.completed_trades', ['boughtTrades' => $boughtTrades, 'soldTrades' => $soldTrades])
@endsection
