<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attack;
use App\Models\Team;

class AttackController extends Controller
{
    public function page($page, Request $request) {
        switch($page){
            case 'sqlinjection': return $this->sqlInjection($request); break;
            default: return (new RedTeamController)->home(); break;
        }
    }

    public function attackComplete($attack){
        $redteam = Team::find($attack->redteam);
        $attack->onAttackComplete();
        $attMsg = "Success: ";
        if($attack->success){
            $attMsg .= "true";
        }else {
            $attMsg .= "false";
        }
        return (new RedTeamController)->home()->with(compact('attMsg'));
    }

    public function sqlInjection(request $request){
        $attack = Attack::get($request->attackName, $request->red, $request->blue);
        $url = $request->url;
        $success = false;
        switch($attack->difficulty){
            case 1: if(empty($url)) $success = true; break;
            case 2: if($url == "'") $success = true; break;
            case 3: if($url == "'--") $success = true; break;
            case 4: if($url == "' or 1=1--") $success = true; break;
            default: break;
        }
        $attack->success = $success;
        Attack::updateAttack($attack);
        return $this->attackComplete($attack);
    }
    
}
