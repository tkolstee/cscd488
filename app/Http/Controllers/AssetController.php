<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Asset;
use App\Models\Asset\AccessAudit;
use App\Models\Blueteam;
use App\Exceptions\InventoryNotFoundException;
use Auth;
use Exception;

class AssetController extends Controller
{
    public function page($page, Request $request){
        switch($page){
            default: return (new BlueTeamController())->home();
        }
    }

    public function useAction(Request $request){
        $action = $request->submit;
        $asset = Asset::get($action);
        $team = Auth::user()->getBlueTeam();
        if($asset->blue == 0)
            $team = Auth::user()->getRedTeam();
        $inv = $team->inventory($asset, 1);
        if($inv == null) throw new InventoryNotFoundException();
        BlueTeamController::removeSellItem($inv->id);
        $inv->removeTrade();
        switch($action){
            case ("AccessAudit"): return $this->accessAudit(); break;
            default: $error = "Invalid Action";
                return (new BlueTeamController())->home()->with(compact('error')); break;
        }
    }

    public function accessAudit(){
        $blueteam = Auth::user()->getBlueTeam();
        $asset = Asset::get("AccessAudit");
        $inv = $blueteam->inventory($asset, 1);
        if($inv == null) throw new InventoryNotFoundException();
        $asset->action($blueteam);
        $inv->reduce();
        $numRemoved = $asset->tags[0];
        unset($asset->tags[0]);
        $actionMsg = "You managed to remove ".$numRemoved." access tokens that were targeting your team.";
        foreach($asset->tags as $teamName){
            $actionMsg += " ".$teamName." had a token.";
        }
        return (new BlueTeamController())->home()->with(compact('actionMsg')); 
    }

}
