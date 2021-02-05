
@extends('layouts.base')
@section('basecontent')
<div class="settingsPage">
    <h4>Name: {{Auth::user()->name}} <a href="/user/changename"><button>Change</button></a><br>
    Username: {{Auth::user()->username}} <a href="/user/changeusername"><button>Change</button></a><br>
    Email: {{Auth::user()->email}} <a href="/user/changeemail"><button>Change</button></a><br>
    <a href="/user/changepassword"><div class="changePass"><button>Change Password</button></div></a></h4>
    <p>
    @if(!empty($blueteam))
        <a href="/blueteam/home" style="color:royalblue;">Blueteam: {{$blueteam->name}}</a><br>
    @endif
    @if(!empty($redteam))
        <a href="/redteam/home" style="color:red;">Redteam: {{$redteam->name}}</a>
    @endif
    </p>
    <br><br><br>
</div>
@endsection