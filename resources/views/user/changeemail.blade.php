@extends('layouts.base')
@section('basecontent')
<div class="settingsPage">
    <br>
    <h4>Change Email from {{Auth::user()->email}} to: </h4>
    <form method="POST" action="/user/changeemail">
    @csrf
        <input name="email" value="{{ Auth::user()->email }}" required autocomplete="email" onFocus="this.select()" autofocus>
        <button type="submit">
            Change Email
        </button>
    </form>
    <br><br><br>
</div>
@endsection