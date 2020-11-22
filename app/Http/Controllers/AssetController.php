<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\Attack;
use App\Models\Game;
use App\Models\Setting;

class AssetController extends Controller
{
    public function prefillTest(){
        if(Asset::all()->isEmpty()){
            $asset = new Asset();
            $asset->name = "TestAssetBlue";
            $asset->type = 1;
            $asset->purchase_cost = 100;
            $asset->ownership_cost = 1;
            $asset->blue = 1;
            $asset->buyable = 1;
            $asset->save();
            $asset = new Asset();
            $asset->name = "TestAssetRed";
            $asset->type = 1;
            $asset->purchase_cost = 200;
            $asset->ownership_cost = 2;
            $asset->blue = 0;
            $asset->buyable = 1;
            $asset->save();
        }
        if(Attack::all()->isEmpty()){
            $attack = new Attack();
            $attack->name = "TestAttackName";
            $attack->difficulty = 3;
            $attack->detection_risk = 3;
            $attack->save();
        }
        if(Game::all()->isEmpty()){
            $game = new Game();
            $game->save();
        }
        if(Setting::get('turn_end_time') == null){
            Setting::set('turn_end_time', '7:00');
        }
        return view('home');
    }
}
