@extends('layouts.base')
@section('basecontent')
    <div style="background-color: red; padding: 0px;">
        <h2>Red Team Content</h2>
        <table width="100%"><tr>
            <td width="50%">
                <img src="blah" alt="messages" height=20 width=20>
                <img src="blah" alt="notifications" height=20 width=20>
            </td>
            @if ($redteam->name  ?? '' != "")
                <td width="50%">
                <strong>{{  $redteam->name ?? '' }} </strong>
                    <br>Cash: {{ $redteam->balance ?? '' }}    Reputation: {{ $redteam->reputation ?? '' }}
                    <br>Energy: {{ App\Models\Redteam::getEnergy($redteam->id) }}
                </td>
            @endif
        </tr></table>
        <br clear>
        <div style="background-color: #F77; padding: 80px; align: center; vertical-align: center;">
            @yield('pagecontent')
        </div>
        <div>
            <a href="/redteam/home"><button>Home</button></a>
            <a href="/redteam/attacks"><button>Attacks</button></a>
            <a href="/redteam/learn"><button>Learn</button></a>
            <a href="/redteam/store"><button>Store</button></a>
            <a href="/redteam/status"><button>Status</button></a>
            <a href="/redteam/settings"><button>Team Settings</button></a>
        </div>
    </div>
@endsection
