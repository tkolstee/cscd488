@extends('layouts.base')
@section('basecontent')
<div class="settingsPage">
    <br>
    <h4>Change Username from {{Auth::user()->username}} to: </h4>
    <form method="POST" action="/user/changeusername">
    @csrf
        <input name="username" value="{{ Auth::user()->username }}" required autocomplete="username" onFocus="this.select()" autofocus>
        <button type="submit">
            Change Username
        </button>
    </form>
    <br><br><br>
</div>
@endsection