<table class="table table-bordered ">
    <thead>
        <th class="blueStoreTd"></th>
        <th>Asset</th>
        <th>Level</th>
        <th>Price</th>
        <th>Seller</th>
    </thead>
    <tbody>
        @foreach ($currentTrades as $trade)
            
            <tr>
            <?php $inv = App\Models\Inventory::find($trade->inv_id); 
                $invAsset = App\Models\Asset::get($inv->asset_name); 
                ?>
                @if($trade->seller_id != Auth::user()->blueteam && $trade->seller_id != Auth::user()->redteam)
                <td class="blueStoreTd"><input type="radio" name="tradeId" value="{{$trade->id}}"></td>
                @else
                <td class="blueStoreTd"></td>
                @endif
                <td>{{$invAsset->name}}</td>
                <td>{{$inv->level}}</td>
                <td>{{$trade->price}}</td>
                <td>{{App\Models\Team::find($trade->seller_id)->name}}</td>
            </tr>
        @endforeach
    </tbody>
</table>
