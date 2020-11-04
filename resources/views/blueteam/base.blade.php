@extends('layouts.base')
@section('basecontent')
    <div style="background-color: blue; padding: 0px;">
        <h2>Blue Team Content</h2>
        <table width="100%"><tr>
            <td width="50%">
                <img src="blah" alt="messages" height=20 width=20>
                <img src="blah" alt="notifications" height=20 width=20>
            </td><td width="50%">
                Revenue: $0    Reputation: 77/100
            </td>
        </tr></table>

        <div style="background-color: #77F; padding: 80px; align: center; vertical-align: center;">
            @yield('pagecontent')
        </div>
        <div>
            <a href="/blueteam/home"><button>Home</button></a>
            <a href="/blueteam/planning"><button>Planning</button></a>
            <a href="/blueteam/status"><button>Status</button></a>
            <a href="/blueteam/store"><button>Store</button></a>
            <a href="/blueteam/training"><button>Training</button></a>
        </div>
    </div>

@endsection
