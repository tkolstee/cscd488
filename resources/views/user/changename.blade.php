@extends('layouts.base')
@section('basecontent')
<div class="settingsPage">
    <br>
    <h4>Change Name from {{Auth::user()->name}} to: </h4>
    <form method="POST" action="/user/changename">
    @csrf
        <input name="name" value="{{ Auth::user()->name }}" required autocomplete="name" onFocus="this.select()" autofocus>
        <button type="submit">
            Change Name
        </button>
    </form>
    <br><br><br>
</div>
@endsection