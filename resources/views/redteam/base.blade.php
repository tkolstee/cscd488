<style>
.redMiddleContainer{
    background: url('../images/redGradiantContainer.jpg'), repeat;
}



</style>


@extends('layouts.base')
@section('basecontent')
    <div class="redTeamContainer">
        <div class="redLogoContainer"> 
            <div class="redLogo">
                 <img src="../images/redTeamLogo1.jpg" alt="messages" >
            </div><!--END redLogo-->
            <div class="h2Container">
                <h2>Red Team Content</h2>
            </div><!--END h2Container-->
        </div><!--END redLogoContainer-->
        <div class="redTeamImage">
                <!--
                <img src="blah" alt="messages" height=50 width=50>
                <img src="blah" alt="notifications" height=20 width=20>
                -->
        </div><!--END blueTeamImage-->
        <div class="blueTeamRevenueStatus">
        @if ($redteam->name  ?? '' != "")
                <div class="statsContainer">
                    <div class="statsNameRed">
                        <div class="loggedIn">
                            <p> Your team name is:</p> 
                        </div>
                    <div class="loggedInName"> 
                        {{  $redteam->name ?? '' }}

                    </div>
                </div>
                    <div class="statsRed">Cash: {{ $redteam->balance ?? '' }}  |  Reputation: {{ $redteam->reputation ?? '' }}</div>
                    <div class="statsRed">Energy: {{ App\Models\Redteam::getEnergy($redteam->id) }}</div>
               
                </div><!--END statsContainer-->
         @else
         <div class="statsContainer">
                    <div class="statsNameRed"><div class="loggedIn"><p> Your team name is:</p> </div><div class="loggedInName"> {{  $redteam->name ?? '' }}</div></div>
                   
                    <div class="statsRed">Revenue: {{ $redteam->balance ?? '' }}  |  Reputation: {{ $redteam->reputation ?? '' }}</div>
                    <div class="statsRed">Turn: {{ App\Models\Game::turnNumber() }}</div>
               
                </div><!--END statsContainer-->
         @endif

        </div><!--END blueTeamRevenueStatus-->
    
           
        
      
        <div class="redMiddleContainer">
            @yield('pagecontent')
        </div>
        <div class="redTeamMenuSelection">
            <ul>
                <li class="startTurn"><a href="/redteam/home">Home</a></li>
                <li class="startTurn"><a href="/redteam/attacks">Attacks</a></li>
                <li class="startTurn"><a href="/redteam/learn">Learn</a></li>
                <li class="startTurn"><a href="/redteam/store">Store</a></li>
                <li class="startTurn"><a href="/redteam/inventory">Inventory</a></li>
                <li class="startTurn"><a href="/redteam/market">Player Market</a></li>
                <li class="startTurn"><a href="/redteam/status">Status</a></li>
                <li class="startTurn"><a href="/redteam/settings">Team Settings</a></li>
            </ul>
        </div><!--End redTeamMenuSelection class-->
    </div><!--End redTeamContainer class-->
@endsection
