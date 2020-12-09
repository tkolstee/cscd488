@extends('blueteam.base')

@section('title', 'Blue Team Inventory')

@section('pagecontent')
    @if($inventory->isEmpty())
        <p>You have no assets.</p>
    @else
        <form method="POST" action="/blueteam/sell">
            @csrf
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

            <button type="submit" class="btn btn-primary">
                Sell
            </button>
        </form>
    @endif
@endsection
