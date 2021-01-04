<table class="table table-bordered table-hover">
    <thead>
        <th></th>
        <th>Name</th>
        <th>Quantity</th>
    </thead>
    <tbody>
        @foreach ($inventory ?? [] as $inv)
            <tr>
                <td><input type="checkbox" name="results[]" value="{{ $inv->asset_name }}"></td>
                <td>{{ App\Models\Asset::get($inv->asset_name)->name }}</td>
                <td>{{$inv->quantity }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
