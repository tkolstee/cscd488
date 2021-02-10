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
                    <div class="statsName">
                        <div class="loggedIn">
                            <p> Your team name is:</p> 
                        </div>
                    <div class="loggedInName"> 
                        {{  $blueteam->name ?? '' }}

                    </div>
                </div>
                    <div class="stats">Revenue: {{ $blueteam->balance ?? '' }}  |  Reputation: {{ $blueteam->reputation ?? '' }}</div>
                    <div class="stats">Turn: {{ App\Models\Game::turnNumber() }}</div>
               
                </div><!--END statsContainer-->
         @else
         <div class="statsContainer">
                    <div class="statsName"><div class="loggedIn"><p> Your team name is:</p> </div><div class="loggedInName"> {{  $blueteam->name ?? '' }}</div></div>
                   
                    <div class="stats">Revenue: {{ $blueteam->balance ?? '' }}  |  Reputation: {{ $blueteam->reputation ?? '' }}</div>
                    <div class="stats">Turn: {{ App\Models\Game::turnNumber() }}</div>
               
                </div><!--END statsContainer-->
         @endif

        </div><!--END blueTeamRevenueStatus-->
        

        <div class="blueMiddleContainer" >   
            @yield('pagecontent')
            @if (!empty(session('buyCart')))
                <h3 class="shoppingCart">Shopping Cart: </h3>
                <?php $cart = session('buyCart'); 
                    $list = array();
                    foreach($cart as $item){
                        if(!isset($list[$item])){
                            $list += [$item => 1];
                        }else{
                            $list[$item]++;
                        }
                    }
                ?>
                <table class="storeFormCancel">
                <thead>
                    <th>Asset</th>
                    <th>Quantity</th>
                    <th>Total Cost</th>
                </thead>
                <tbody >
                @foreach ($list as $name => $quantity)
                <tr>
                    <td style="width:30%;">{{$name}}</td>
                    <td>{{$quantity}}</td>
                    <td>Total: <?php echo $quantity * (App\Models\Asset::getByName($name)->purchase_cost); ?></td>
                   <form  method="POST" action="/blueteam/cancel">
                        @csrf
                        <input type="hidden" name="currentPage" value="{{$currentPage ?? 0}}">
                        <input type="hidden" name="cart" value="buy">
                        <td><button type="submit" formaction="/blueteam/cancel" 
                            class="btn btn-primary4" 
                            name="{{"cancel[" . $name . "]"}}">
                            Remove 
                            @if($quantity > 1)
                            One
                            @endif
                        </button></td>
                    </form>
                </tr>
                @endforeach
                </tbody>
                </table>
            @endif
            @if (!empty(session('sellCart')))
                <h3 class="shoppingCart">Sell Cart</h3>
                <?php $cart = session('sellCart'); 
                     $list = array();
                     foreach($cart as $item){
                         $name = App\Models\Asset::get(App\Models\Inventory::find($item)->asset_name)->name;
                         if(!isset($list[$item])){
                             $list += [$item => [$name, 1]];
                         }else{
                             $list[$item][1]++;
                         }
                     }
                ?>
                <table class="storeFormCancel">
                <thead>
                    <th>Asset</th>
                    <th>Quantity</th>
                    <th>Total Cost</th>
                </thead>
                <tbody>
                @foreach ($list as $id=>$nameQuantity)
                <tr>
                    <?php $asset = App\Models\Asset::getByName($nameQuantity[0]); ?>
                    <td>{{$nameQuantity[0]}}</td>
                    <td>{{$nameQuantity[1]}}</td>
                    <td>Total: <?php echo $nameQuantity[1] * $asset->purchase_cost; ?></td>
                    <?php $inv = App\Models\Inventory::find($id); ?>
                    @if(in_array("Targeted", $asset->tags) && $inv->info != null)
                    <td>Target: {{$inv->info}}</td>
                    @endif
                    <form  method="POST" action="/blueteam/cancel">
                        @csrf
                        <input type="hidden" name="cart" value="sell">
                        <input type="hidden" name="currentPage" value="{{$currentPage ?? 0}}">
                        <td > <button  type="submit" formaction="/blueteam/cancel" 
                            class="btn btn-primary4" 
                            name="{{"cancel[" . $id . "]"}}">
                            Remove 
                            @if($nameQuantity[1] > 1)
                            One
                            @endif
                            </button></td>
                    </form>
                </tr>
                @endforeach
                </tbody>
                </table>
            @endif
        </div><!--END blueMiddleContainer-->

        <div class="blueTeamMenuSelection">
            <ul>
                <li class="startTurn"><a href="/blueteam/home">Home</a></li>
                <li class="startTurn"><a href="/blueteam/news">News</a></li>
                <li class="startTurn"><a href="/blueteam/attacks">Attacks</a></li>
                <li class="startTurn"><a href="/blueteam/planning">Planning</a></li>
                <li class="startTurn"><a href="/blueteam/status">Status</a></li>
                <li class="startTurn"><a href="/blueteam/store">Store</a></li>
                <li class="startTurn"><a href="/blueteam/inventory">Inventory</a></li>
                <li class="startTurn"><a href="/blueteam/training">Training</a></li>
                <li class="startTurn"><a href="/blueteam/leaderboard">Leaderboard</a></li>
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
