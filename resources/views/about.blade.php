<style>
    .aboutPage{
    background: url('/images/repeatingBackground.jpg'), center no-repeat;
}
</style>

@extends('layouts.base')
@section('basecontent')
<div class="aboutPage">
    <h3 class="aboutJanus">About Janus</h3>
    <p>Janus is designed to teach internet security skills to those who are not specifically studying Security.<br>
    Users will have the chance to play as the red and blue teams in order to learn offensive security tactics<br>
    as well as defensive security tactics.</p>
    <h3 class="aboutJanus">Blue Team</h3>
    <p>Users can choose to create their own Blue Team or join another user's Blue Team. <br>
    The Blue Team's goal is to use their turn to buy or sell assets that will increase revenue <br>
    and protection from the Red Teams' attacks. Blue Teams will get one turn per day which refreshes <br>
    at the start time designated by the server admin. Users will add assets to the shopping cart <br>
    for their Blue Team, sell assets they own, or upgrade assets they own.</p>

    <h3 class="aboutJanus">Red Team</h3>
    <p>Users can each make their own Red Team. The Red Team is responsible for attacking other Blue Teams <br>
    in order to make money, earn bonuses, and damage the Blue Team. Red Teams do not have designated turns, <br>
    but they do have an energy amount that refreshes at the designated turn-reset time. Red Teams can <br>
    also buy or sell assets in order to help them attack</p>

    <h3 class="aboutJanus">Assets</h3>
    <p>Assets all have different ways to affect Red and Blue Teams. Some assets affect the <br>
    detection risk of certain attacks, affect the difficulty of certain attacks, affect attack <br>
    prerequisite requirements, help aquire bonuses into certain BlueTeams for future attack, or <br>
    affect the money lost or gained from certain attacks. And some assets are owned to generate <br>
    revenue for the Blue Team every turn.</p>
    <p>All assets have different effects against different attacks, so a wide variety of defensive and offensive <br>
    assets are required to be successful. Each asset can also be upgraded to multiply whatever effects <br>
    they do have. Each upgrade to an asset costs more, and when the asset is sold you will only <br>
    receive the cost of the last upgrade in addition to the original price.</p>
    </div>
@endsection

