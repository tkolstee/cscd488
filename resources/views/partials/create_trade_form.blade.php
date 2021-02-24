<table class="table table-bordered">
@foreach($inventories as $inv)
    <?php $invAsset = App\Models\Asset::get($inv->asset_name); ?>
    <tr>
        <td style="width:10%;"><input type="radio" name="inv_id" value="{{$inv->id}}" /></td>
        <td style="width:70%;">{{$invAsset->name}}
        @if($inv->level > 1)
            {{" " . $inv->level}}
        @endif
        </td>
    </tr>
@endforeach
</table>
<h4>Enter Price</h4>
<input type="number" id="price" min="0" name="price" style="width:20%;" /><br>
<button type="submit" class="btn btn-primary">
    Create Trade
</button>