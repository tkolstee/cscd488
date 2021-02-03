<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attack;

class LearnController extends Controller
{
    public function page($page, Request $request){
        switch($page){
            case 'learn': return $this->home($request); break;
            case 'backdoorbasic': return $this->backdoor($request); break;
            case 'backdoorprivileged': return $this->backdoor($request); break;
            case 'backdoorpwned': return $this->backdoor($request); break;
            case 'dos': return $this->dos($request); break;
            case 'ddos': return $this->dos($request); break;
            case 'driveby': return $this->driveby($request); break;
            case 'fuzzing': return $this->fuzzing($request); break;
            case 'implantedhwoffice': return $this->implantedhw($request); break;
            case 'implantedhwserver': return $this->implantedhw($request); break;
            case 'malvertise': return $this->malvertise($request); break;
            case 'mitm' : return $this->mitm($request); break;
            case 'phishingattachment': return $this->phishing($request); break;
            case 'phishingcredentials': return $this->phishing($request); break;
            case 'phishinglink': return $this->phishing($request); break;
            case 'sqlinjection': return $this->sqlInjection($request); break;
            case 'supplychaindev': return $this->supplychain($request); break;
            case 'supplychainhw': return $this->supplychain($request); break;
            case 'supplychainsw': return $this->supplychain($request); break;
            case 'synflood': return $this->synFlood($request); break;
            case 'wirelessnetwork': return $this->wirelessnetwork($request); break;
            case 'xss': return $this->xss($request); break;
            default: return $this->home($request); break;
        }
    }

    public function backdoor(request $request){
        $step = $this->getStep($request);
        return view('redteam.learn.backdoor')->with(compact('step'));
    }

    public function dos(request $request){
        $step = $this->getStep($request);
        return view('redteam.learn.dos')->with(compact('step'));
    }

    public function driveby(request $request){
        $step = $this->getStep($request);
        return view('redteam.learn.driveby')->with(compact('step'));
    }

    public function fuzzing(request $request){
        $step = $this->getStep($request);
        return view('redteam.learn.fuzzing')->with(compact('step'));
    }

    public function implantedhw(request $request){
        $step = $this->getStep($request);
        return view('redteam.learn.implantedhw')->with(compact('step'));
    }

    public function malvertise(request $request){
        $step = $this->getStep($request);
        return view('redteam.learn.malvertise')->with(compact('step'));
    }

    public function mitm(request $request){
        $step = $this->getStep($request);
        return view('redteam.learn.mitm')->with(compact('step'));
    }

    public function phishing(request $request){
        $step = $this->getStep($request);
        return view('redteam.learn.phishing')->with(compact('step'));
    }

    public function supplychain(request $request){
        $step = $this->getStep($request);
        return view('redteam.learn.supplychain')->with(compact('step'));
    }

    public function synFlood(request $request){
        $step = $this->getStep($request);
        return view('redteam.learn.synflood')->with(compact('step'));
    }

    public function sqlInjection(request $request){
        $step = $this->getStep($request);
        return view('redteam.learn.sqlinjection')->with(compact('step'));
    }

    public function wirelessnetwork(request $request){
        $step = $this->getStep($request);
        return view('redteam.learn.wirelessnetwork')->with(compact('step'));
    }

    public function xss(request $request){
        $step = $this->getStep($request);
        return view('redteam.learn.xss')->with(compact('step'));
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
