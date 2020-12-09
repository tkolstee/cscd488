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
            default: return $this->home($request); break;
        }
    }

    public function sqlInjection(Request $request){
        $step = $request->step;
        if(empty($step)) $step = 1;
        else $step += $request->progress;
        if($step < 1) $step = 1;
        if($step > 4) $step = 4;
        $result = $request->result;

        return view('redteam.learn.sqlinjection')->with(compact('step'));
    }

    public function home(Request $request){
        $attacks = Attack::getAll();
        return view('redteam.learn')->with(compact('attacks'));
    }
}
