<table class="table table-bordered ">
    <thead>
        <th></th>
        <th>Name</th>
        <th>Purchase Cost</th>
        <th>Ownership Cost</th>
        <th>Owned</th>
    </thead>
    <tbody>
        @foreach ($assets as $asset)
            <tr>
                <td class="blueStoreTd"><input type="checkbox" name="results[]" value="{{ $asset->class_name }}"></td>
                <td>{{$asset->name}}</td>
                <td>{{$asset->purchase_cost}}</td>
                <td>{{$asset->ownership_cost}}</td>
                <td>@if ($ownedAssets->contains($asset)) ✅ @endif</td>
            </tr>
        @endforeach
    </tbody>
</table>
