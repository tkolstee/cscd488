@extends('minigame.base')

@section('title', 'SQL Injection Attack')

@section('pagecontent')
@if ($attack->difficulty == 1)
<h2>View {{ $blueteam->name }} products for sale.</h2>
@elseif ($attack->difficulty == 2)
<h2>See if {{ $blueteam->name }} has SQL Injection vulnerability</h2>
@elseif ($attack->difficulty == 3)
<h2>Get past user validation to access the products for sale.</h2>
@elseif ($attack->difficulty == 4)
<h2>Get past user validation to access all products (for sale or not).</h2>
@elseif ($attack->difficulty == 5)
<h2>Sadly attempt to break {{ $blueteam->name }}'s indestructable firewall.</h2>
@endif

<strong>Difficulty: {{ $attack->difficulty }}</strong>
<form method="POST" action="/attack/sqlinjection">
    @csrf

    <div class="form-group row">
        <label for="url" class="col-md-4 col-form-label text-md-right">
            http://{{ $blueteam->name }}.com/products?type=for_sale</label>
        <input type="text" id="url" name="url" >
        <input type="hidden" name="attackName" value="{{ $attack->class_name }}">
        <input type="hidden" name="red" value="{{ $redteam->id }}">
        <input type="hidden" name="blue" value="{{ $blueteam->id }}">
    </div>

    <div class="form-group row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" class="btn btn-primary">
                Enter URL
            </button>

            
        </div>
    </div>
</form>
@endsection
