@extends('redteam.base')

@section('title', 'Red Team Market')

@section('pagecontent')
<a href="/redteam/market"><button type="submit" class="btn btn-primary2">Return to Market</button></a>
<h4>Your Current Trades</h4>
    @if($currentTrades->isEmpty())
        <p>You have no current trades active.</p>
    @else
    <?php $currentPage = $currentTrades->currentPage(); ?>
    <div >
        <table class="table table-bordered ">
            <thead>
                <th>Asset</th>
                <th>Price</th>
                <th></th>
            </thead>
            <tbody>
                @foreach($currentTrades as $trade)
                <?php $inv = App\Models\Inventory::find($trade->inv_id);
                    $asset = App\Models\Asset::get($inv->asset_name); ?>
                    <tr>
                        <td>{{$asset->name}}
                            @if($inv->level > 1)
                                {{" " . $inv->level}}
                            @endif
                        </td>
                        <td>{{$trade->price}}</td>
                        <td><form method="POST" action="/redteam/canceltrade"> @csrf
                            <button class="btn btn-primary4" type="submit" name="cancelTradeSubmit" value="{{$trade->id}}">Cancel</button>    
                        </form></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @include('partials.pagination', ['paginator' => $currentTrades])
    </div>
    @endif
@endsection
