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
    <div class="redTeamContainer">
        <div class="redLogoContainer"> 
            <div class="redLogo">
                 <img src="../images/blueTeamLogo1.jpg" alt="messages" >
            </div><!--END redLogo-->
            <div class="h2Container">
                <h2>Red Team Content</h2>
            </div><!--END h2Container-->
        </div><!--END redLogoContainer-->

        
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
        <div class="redTeamMenuSelection">
            <ul>
                <li class="startTurn"><a href="/redteam/home">Home</a></li>
                <li class="startTurn"><a href="/redteam/attacks">Attacks</a></li>
                <li class="startTurn"><a href="/redteam/learn">Learn</a></li>
                <li class="startTurn"><a href="/redteam/store">Store</a></li>
                <li class="startTurn"><a href="/redteam/inventory">Inventory</a></li>
                <li class="startTurn"><a href="/redteam/status">Status</a></li>
                <li class="startTurn"><a href="/redteam/settings">Team Settings</a></li>
            </ul>
        </div><!--End redTeamMenuSelection class-->
    </div><!--End redTeamContainer class-->
@endsection
