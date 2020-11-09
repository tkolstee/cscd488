<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;

class AssetController extends Controller
{
    public function prefill(){
        if(isempty(Asset::all())){
            $asset = new Asset();
            $asset->name = "TestAssetName";
            $asset->type = 1;
            $asset->purchase_cost = 100;
            $asset->ownership_cost = 1;
            $asset->blue = 1;
            $asset->buyable = 1;
            $asset->save();
            $asset = new Asset();
            $asset->name = "TestAssetName2";
            $asset->type = 1;
            $asset->purchase_cost = 200;
            $asset->ownership_cost = 2;
            $asset->blue = 0;
            $asset->buyable = 1;
            $asset->save();
        }
        return view('home');
    }
}
