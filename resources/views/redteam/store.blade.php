@extends('redteam.base')

@section('title', 'Red Team Store')

@section('pagecontent')
    <p>This is the red team store.</p>
    @if(count($assets ??[]) == 0)
        <p>No items are available for purchase right now.</p>
    @else
        <form method="POST" action="/redteam/buy">
            @csrf
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
            <button type="submit" class="btn btn-primary">
                Purchase
            </button>
        </form>
    @endif
@endsection
