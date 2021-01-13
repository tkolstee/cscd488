<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attack;
use App\Models\Team;
use App\Exceptions\AttackNotFoundException;
use Exception;

class AttackController extends Controller
{
    public function page($page, Request $request) {
        switch($page){
            case 'sqlinjection': return $this->sqlInjection($request); break;
            case 'synflood': return $this->synFlood($request); break;
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

    public function synFlood(request $request){
        $attack = Attack::find($request->attID);
        if($attack == null) throw new Exception($request->attID);//AttackNotFoundException();
        if($request->result1 == 1 && $request->result2 == 1) $success = true;
        else $success = false;
        $attack->setSuccess($success);
        return $this->attackComplete($attack);
    }

    public function sqlInjection(request $request){
        $attack = Attack::find($request->attID);
        if($attack == null) throw new AttackNotFoundException();
        $url = $request->url;
        $success = false;
        switch($attack->difficulty){
            case 1: $success = true; break;
            case 2: if($url == "'") $success = true; break;
            case 3: if($url == "'--") $success = true; break;
            case 4: if($url == "' or 1=1--") $success = true; break;
            default: break;
        }
        $attack->setSuccess($success);
        return $this->attackComplete($attack);
    }
    
}
