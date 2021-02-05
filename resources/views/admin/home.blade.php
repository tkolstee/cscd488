@extends('admin.base')
@section('title', 'admin home page')
@section('content')
    <h1>Admin Home Page</h1>
    Turn number: {{ App\Models\Game::turnNumber() }}
    <form method="POST">@csrf<button type="submit" name="action" value="next-turn">Next Turn</button></form>
    <a href="/admin/playerRegistration"><button>Register Players</button></a> 
    <form method="POST"> 
        @csrf 
        <input type="hidden" name="action" value="toggle-prereqs">
        <input type="checkbox" onChange="this.form.submit()"@if(App\Models\Game::prereqsDisabled()) checked @endif>
        Disable Attack Prereqs
    </form>
@endsection
