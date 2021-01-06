<style>
.blueMiddleContainer{
    background: url('../images/h3Background.jpg'), repeat;
}
.h2Container{
    /*background: url('../images/h3Background.jpg'), no-repeat;*/
}

</style>
@extends('layouts.base')
@section('basecontent')
    <div class="blueTeamContainer">
        <div class="blueLogoContainer"> 
            <div class="blueLogo">
                <img src="../images/blueTeamLogo1.jpg" alt="messages" >
            </div><!--END blueLogo-->
            <div class="h2Container">
                <h2>Blue Team Content</h2>
            </div><!--END h2Container-->
        </div><!--END blueLogoContainer-->
       <!--<div class="testtest"></div>-->
        <div class="blueTeamImage">
                <!--
                <img src="blah" alt="messages" height=50 width=50>
                <img src="blah" alt="notifications" height=20 width=20>
                -->
        </div><!--END blueTeamImage-->
        <div class="blueTeamRevenueStatus">
            @if ($blueteam->name  ?? '' != "")
                <div class="statsContainer">
                    <div class="statsName"><div class="loggedIn"><p> You are logged in as:</p> </div><div class="loggedInName"> {{  $blueteam->name ?? '' }}</div></div>
                    <div class="stats">Revenue: {{ $blueteam->balance ?? '' }}  |  Reputation: {{ $blueteam->reputation ?? '' }}</div>
                    <div class="stats">Turn: {{ App\Models\Game::turnNumber() }}</div>
                </div><!--END statsContainer-->
            @endif

        </div><!--END blueTeamRevenueStatus-->
        

        <div class="blueMiddleContainer" >   
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
        </div><!--END blueMiddleContainer-->

        <div class="blueTeamMenuSelection">
            <ul>
                <li class="startTurn"><a href="/blueteam/home">Home</a></li>
                <li class="startTurn"><a href="/blueteam/planning">Planning</a></li>
                <li class="startTurn"><a href="/blueteam/status">Status</a></li>
                <li class="startTurn"><a href="/blueteam/store">Store</a></li>
                <li class="startTurn"><a href="/blueteam/training">Training</a></li>
                <li class="startTurn"><a href="/blueteam/settings">Team Settings</a></li>
                @if ($blueteam ?? null != null)
                @if (($turn ?? 0) != 1)
                <li class="startTurn2"><a href="/blueteam/endturn">End Turn</a></li>
                @else
                <li class="startTurn2"><a  href="/blueteam/startturn">Start Turn</a></li>
                @endif
                @endif
            </ul>
        </div><!--End blueTeamMenuSelection class-->
    </div>

@endsection
