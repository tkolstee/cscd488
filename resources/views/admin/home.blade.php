@extends('admin.base')
@section('title', 'admin home page')
@section('content')
    <h1>Admin Home Page</h1>
    Turn number: {{ App\Models\Game::turnNumber() }}
    <form method="POST">@csrf<button type="submit" name="action" value="next-turn">Next Turn</button></form>
    <a href="/admin/userSignUp"><button>User Sign Up</button></a> 
@endsection
