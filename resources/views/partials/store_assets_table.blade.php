<table class="table table-bordered ">
    <thead>
        <th class="blueStoreTd"></th>
        <th>Name</th>
        <th>Purchase Cost</th>
        <th>Ownership Cost</th>
        <th>Revenue Generated Per Turn</th>
        <th>Owned</th>
        <th class="descriptionTd" >Description</th>
    </thead>
    <tbody>
        @foreach ($assets as $asset)
            <tr>
                <td class="blueStoreTd"><input type="checkbox" name="results[]" value="{{ $asset->class_name }}"></td>
                <td>{{$asset->name}}</td>
                <td>{{$asset->purchase_cost}}</td>
                <td>
                @if($asset->ownership_cost >= 0)
                {{$asset->ownership_cost}}
                @else
                0
                @endif
                </td>
                <td>
                @if($asset->ownership_cost < 0)
                {{$asset->ownership_cost * -1}}
                @else
                0
                @endif
                </td>
                <td>@if ($ownedAssets->contains($asset)) ✅ @endif</td>
                <td class="descriptionTd">{{$asset->description}}</td>
            </tr>
        @endforeach
    </tbody>
</table>
