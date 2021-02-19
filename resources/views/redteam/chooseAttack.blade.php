@extends('redteam.base')

@section('title', 'Red Team Home')

@section('pagecontent')
@if ($possibleAttacks ?? [] !== [])
<h3>Select a method of attack against {{ $blueteam->name }}:</h3>
<form method="POST" action="/redteam/performattack">
    @csrf
    <table class="table table-bordered ">
        <thead>
            <th class="attackTd"></th>
            <th>Name</th>
            <th>Energy Cost</th>
        </thead>
        <tbody>
            @foreach ($possibleAttacks ?? [] as $attack)
                <tr>
                    <td class="attackTd"><input type="radio" name="result" id="{{ $attack->class_name }}" value="{{ $attack->class_name }}"></td>
                    <td>{{$attack->name}}</td>
                    <td>{{$attack->energy_cost}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @include('partials.pagination', ['paginator' => $possibleAttacks])
    <input type="hidden" name="blueteam" value="{{ $blueteam->name }}">
    <div class="form-group row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" class="btn btn-primary">
                Choose Attack
            </button>
        </div>
    </div>
</form>
@else
    <strong>You have no methods of attack against {{ $blueteam->name }}</strong><br>
    <a href="/redteam/startattack"><button>Pick a Different Team</button></a>
@endif
@endsection
