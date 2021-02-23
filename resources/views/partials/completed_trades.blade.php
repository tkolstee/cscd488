<h4>Your Sold Trades</h4>
    @if($soldTrades->isEmpty())
        <p>You have not sold any trades.</p>
    @else
    <div >
        <table class="table table-bordered ">
            <thead>
                <th>Asset</th>
                <th>Price</th>
                <th>Buyer</th>
            </thead>
            <tbody>
                @foreach($soldTrades as $trade)
                <?php $asset = App\Models\Asset::getByName($trade->asset_name); ?>
                    <tr>
                        <td>{{$asset->name}}
                            @if($trade->asset_level > 1)
                                {{" " . $trade->asset_level}}
                            @endif
                        </td>
                        <td>{{$trade->price}}</td>
                        <td>{{App\Models\Team::find($trade->buyer_id)->name}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @include('partials.pagination', ['paginator' => $soldTrades])
    </div>
    @endif
    <h4>Your Bought Trades</h4>
    @if($boughtTrades->isEmpty())
        <p>You have not purchased any trades.</p>
    @else
    <div >
        <table class="table table-bordered ">
            <thead>
                <th>Asset</th>
                <th>Price</th>
                <th>Seller</th>
            </thead>
            <tbody>
                @foreach($boughtTrades as $trade)
                <?php $asset = App\Models\Asset::getByName($trade->asset_name); ?>
                    <tr>
                        <td>{{$asset->name}}
                            @if($trade->asset_level > 1)
                                {{" " . $trade->asset_level}}
                            @endif
                        </td>
                        <td>{{$trade->price}}</td>
                        <td>{{App\Models\Team::find($trade->seller_id)->name}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @include('partials.pagination', ['paginator' => $boughtTrades])
    </div>
    @endif