@extends('redteam.base')

@section('title', 'Red Team Market')

@section('pagecontent')
<h4>Red Team Player Market</h4>
    <a href="/redteam/createtrade"><button type="submit" class="btn btn-primary2">Create Trade</button></a>
    @if($currentTrades->isEmpty())
        <p>There are no available trades right now.</p>
    @else
    <?php $currentPage = $currentTrades->currentPage(); ?>
    <div >
        <form class="storeFormInventory" method="POST" action="/redteam/market">
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
@endsection
