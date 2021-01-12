<table class="table table-bordered table-hover">
    <thead>
        <th></th>
        <th>Name</th>
        <th>Quantity</th>
        <th>Level</th>
    </thead>
    <tbody>
        @foreach ($inventory ?? [] as $inv)
        <?php $invAsset = App\Models\Asset::get($inv->asset_name); ?>
            <tr>
                <td><input type="checkbox" name="results[]" value="{{ $inv->asset_name . $inv->level }}"></td>
                <td>{{ $invAsset->name }}</td>
                <td>{{$inv->quantity }}</td>
                <td>{{$inv->level }}</td>
                @if($inv->level < 3)
                    @if($invAsset->blue == 1)    
                        <td><button type="submit" formaction="/blueteam/upgrade" 
                            class="btn btn-primary" 
                            name="submit" value="{{$invAsset->class_name . $inv->level }}">
                            Upgrade Cost: {{ $inv->getUpgradeCost() }}</button>
                        </td>
                    @endif
                    @if($invAsset->blue == 0)
                        <td><button type="submit" formaction="/redteam/upgrade" 
                            class="btn btn-primary" 
                            name="submit" value="{{$invAsset->class_name . $inv->level }}">
                            Upgrade Cost: {{ $inv->getUpgradeCost() }}</button>
                        </td>
                    @endif
                    
                @endif
            </tr>
        @endforeach
    </tbody>
</table>