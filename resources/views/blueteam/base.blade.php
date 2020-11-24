@extends('layouts.base')
@section('basecontent')
    <div style="background-color: blue; padding: 0px;">

        <h2>Blue Team Content</h2>
        <table width="100%"><tr>
            <td width="50%">
                <img src="blah" alt="messages" height=20 width=20>
                <img src="blah" alt="notifications" height=20 width=20>
            </td>
            @if ($blueteam->name  ?? '' != "")
                <td width="50%">
                <strong>{{  $blueteam->name ?? '' }} </strong>
                    <br>Revenue: {{ $blueteam->balance ?? '' }}    Reputation: {{ $blueteam->reputation ?? '' }}
                    <br>Turn: {{ App\Models\Game::turnNumber() }}
                </td>
            @endif
        </tr></table>

        <div style="background-color: #77F; padding: 80px; align: center; vertical-align: center;">
            @yield('pagecontent')
            @if (!empty(session('buyCart')))
                <p>Shopping Cart: </p>
                <?php $cart = session('buyCart'); ?>
                @foreach ($cart as $item)
                    {{ $item }} <br>
                @endforeach
            @endif
            @if (!empty(session('sellCart')))
                <p>Sell Cart</p>
                <?php $cart = session('sellCart'); ?>
                @foreach ($cart as $item)
                    {{ $item }} <br>
                @endforeach
            @endif
        </div>
        <div>
            <a href="/blueteam/home"><button>Home</button></a>
            <a href="/blueteam/planning"><button>Planning</button></a>
            <a href="/blueteam/status"><button>Status</button></a>
            <a href="/blueteam/store"><button>Store</button></a>
            <a href="/blueteam/training"><button>Training</button></a>
            <a href="/blueteam/settings"><button>Team Settings</button></a>            
            @if ($blueteam ?? null != null)
                @if (($turn ?? 0) != 1)
                <a href="/blueteam/endturn"><button>End Turn</button></a>
                @else
                <a href="/blueteam/startturn"><button>Start Turn</button></a>
                @endif
            @endif
        </div>
    </div>

@endsection
