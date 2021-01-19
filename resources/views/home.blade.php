<style>
    .homeContainer{
    height: 100%;
    background: url('/images/repeatingBackground.jpg'), center no-repeat;
}
.card {
    background: url('/images/redVsBlue1.jpg'), center no-repeat;
   
}
.RteamChoice{
    background: url('/images/redSelection.jpg'), no-repeat;
}
.RteamChoice:hover {
    background: url('/images/redSelectionHover.jpg'), no-repeat;
}
.BteamChoice{
    background: url('/images/blueSelection.jpg'), no-repeat;
}
.BteamChoice:hover{
    background: url('/images/blueSelectionHover.jpg'), no-repeat;
}

/*#rForm_container{
    background:url('images/redGrad.jpg'), center no-repeat;
    height:100%;
    width:100%;
}
#lForm_container{
    background:url('images/blueGrad.jpg'), center no-repeat;
    height:100%;
    width:100%;
}
*/

</style>

@extends('layouts.base')

@section('basecontent')
<div class="homeContainer">
    <div class="container">
    <div class="chooseTeam"><p> Choose Your Team </p></div><!--END chooseTeam-->
        <div class="Bcontainer">
        <a href="/blueteam/home"><div class="BteamChoice"></div></a><!--END BteamChoice-->
                <div class="blueButtonChoice">
                    <a href="/blueteam/home">
                        <button class="blueButton" type="submit" >Blue Team</button>
                    </a>
                </div><!--blueButtonChoice-->
        </div><!--END Bcontainer-->
        <div class="RedContainer">
            <a href= "/redteam/home"><div class="RteamChoice"></div></a><!--END RteamChoice-->
                <div class="redButtonChoice">
                        <a href="/redteam/home">
                            <button class="redButton"type="submit" >Red Team</button>
                        </a>
                </div><!--END redButtonChoice-->
        </div><!--END RedContainer-->
    </div><!--END container-->
</div><!--END homeContainer-->


@endsection
