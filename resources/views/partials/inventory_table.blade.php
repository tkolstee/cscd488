<table class="table table-bordered">
    <thead>
        <th class="blueInvTd">Sell</th>
        <th>Name</th>
        <th>Quantity</th>
    </thead>
    <tbody>
        <?php $currentCart = array_count_values(session('sellCart') ?? []); ?>
        @foreach ($inventory ?? [] as $inv)
        <?php $invAsset = App\Models\Asset::get($inv->asset_name); 
        $inArray = in_array("Action",$invAsset->tags); ?>
            <tr>
                <td class="blueInvTd">
                <?php if(isset($currentCart[$inv->id])){
                    $inCart = true;
                    $amountInCart = $currentCart[$inv->id];
                }else{
                    $inCart = false;
                    $amountInCart = 0;
                }
                    ?>
                @if($inv->quantity > $amountInCart)
                    <input type="number" min="0" name="results[{{$inv->id}}]" max="{{$inv->quantity - $amountInCart}}">
                @endif
                </td>
                <td 
                    title="{{$invAsset->description}}
                    <?php if($invAsset->ownership_cost > 0) echo " Ownership Cost: " . $invAsset->ownership_cost;
                    else echo "Revenue Gained Per Turn: " . (-1 * $invAsset->ownership_cost); ?>"
                    >{{ $inv->getAssetName() }}</td>
                <td>{{$inv->quantity }}</td>
                @if(in_array("Targeted", $invAsset->tags)) 
                    @if($inv->info != null)
                        <td>Target: {{ $inv->info }}</td>
                    @else
                    <td><button type="submit" formaction="/blueteam/picktarget" 
                        class="btn btn-primaryInventory" 
                        name="submit" value="{{$inv->id }}">
                        Pick Target</button>
                    </td>
                    @endif
                @endif
                @if(!$inArray && !in_array("Targeted", $invAsset->tags))
                    <td>Level: {{$inv->level }}</td>
                    @if($inv->level < 3 )
                        @if($invAsset->blue == 1)    
                            <td><button type="submit" formaction="/blueteam/upgrade" 
                                class="btn btn-primaryInventory" 
                                name="submit" value="{{$inv->id }}">
                                Upgrade Cost: {{ $inv->getUpgradeCost() }}</button>
                            </td>
                        @endif
                        @if($invAsset->blue == 0)
                            <td><button type="submit" formaction="/redteam/upgrade" 
                                class="btn btn-primaryInventory" 
                                name="submit" value="{{$inv->id }}">
                                Upgrade Cost: {{ $inv->getUpgradeCost() }}</button>
                            </td>
                        @endif
                    @endif
                @endif
                @if($inArray)
                    <td><button type="submit" formaction="/asset" 
                        class="btn btn-primaryInventory" 
                        name="submit" value="{{$invAsset->class_name }}">
                        Use</button>
                    </td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>
