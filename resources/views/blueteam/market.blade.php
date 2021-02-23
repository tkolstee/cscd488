@extends('blueteam.base')

@section('title', 'Blue Team Market')

@section('pagecontent')
<h4>Blue Team Player Market</h4>
    <a href="/blueteam/createtrade"><button type="submit" class="btn btn-primary2">Create Trade</button></a>
    <a href="/blueteam/currenttrades"><button type="submit" class="btn btn-primary2">View Current Trades</button></a>
    @if($currentTrades->isEmpty())
        <p>There are no available trades right now.</p>
    @else
    <?php $currentPage = $currentTrades->currentPage(); ?>
    <div >
        <form class="storeFormInventory" method="POST" action="/blueteam/market">
            @csrf
            @include('partials.trade_table', ['currentTrades' => $currentTrades])
            <input type="hidden" name="currentPage" value="{{$currentPage}}">
            <button type="submit" class="btn btn-primary" name="tradeSubmit">
                Trade
            </button>
        </form>
        @include('partials.pagination', ['paginator' => $currentTrades])
    </div>
    @endif
    <a href="/blueteam/completedtrades"><button type="submit" class="btn btn-primary2">View Completed Trades</button></a>
@endsection
