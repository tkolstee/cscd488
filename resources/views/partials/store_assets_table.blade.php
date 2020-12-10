<table class="table table-bordered table-hover">
    <thead>
        <th></th>
        <th>Name</th>
        <th>Purchase Cost</th>
        <th>Ownership Cost</th>
    </thead>
    <tbody>
        @foreach ($assets as $asset)
            <tr>
                <td><input type="checkbox" name="results[]" value="{{ $asset->class_name }}"></td>
                <td>{{$asset->name}}</td>
                <td>{{$asset->purchase_cost}}</td>
                <td>{{$asset->ownership_cost}}</td>
            </tr>
        @endforeach
    </tbody>
</table>
