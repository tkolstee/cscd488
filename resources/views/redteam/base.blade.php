@extends('layouts.base')
@section('basecontent')
    <div style="background-color: red; padding: 0px;">
        <h2>Red Team Content</h2>
        <table width="100%"><tr>
            <td width="50%">
                <img src="blah" alt="messages" height=20 width=20>
                <img src="blah" alt="notifications" height=20 width=20>
            </td><td width="50%">
                Cash: $0    Reputation: 07/100
            </td>
        </tr></table>
        <br clear>
        <div style="background-color: #F77; padding: 80px; align: center; vertical-align: center;">
            @yield('pagecontent')
        </div>
        <div>
            <a href="/redteam/home"><button>home</button></a>
            <a href="/redteam/attacks"><button>attacks</button></a>
            <a href="/redteam/learn"><button>learn</button></a>
            <a href="/redteam/store"><button>store</button></a>
            <a href="/redteam/status"><button>status</button></a>
        </div>
    </div>
@endsection
