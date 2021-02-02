<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attack;

class LearnController extends Controller
{
    public function page($page, Request $request){
        switch($page){
            case 'learn': return $this->home($request); break;
            case 'sqlinjection': return $this->sqlInjection($request); break;
            case 'synflood': return $this->synFlood($request); break;
            case 'malvertise': return $this->malvertise($request); break;
            case 'mitm' : return $this->mitm($request); break;
            default: return $this->home($request); break;
        }
    }

    public function malvertise(request $request){
        $step = $this->getStep($request);
        return view('redteam.learn.malvertise')->with(compact('step'));
    }

    public function mitm(request $request){
        $step = $this->getStep($request);
        return view('redteam.learn.mitm')->with(compact('step'));
    }

    public function synFlood(request $request){
        $step = $this->getStep($request);
        return view('redteam.learn.synflood')->with(compact('step'));
    }

    public function sqlInjection(Request $request){
        $step = $this->getStep($request);
        return view('redteam.learn.sqlinjection')->with(compact('step'));
    }

    public static function getStep(request $request){
        $step = $request->step;
        if(empty($step)) $step = 1;
        else $step += $request->stepChange;
        if($step < 1) $step = 1;
        if($step > 4) $step = 4;
        return $step;
    }

    public function home(Request $request){
        $attacks = Attack::getLearnableAttacks();
        return view('redteam.learn')->with(compact('attacks'));
    }
}
