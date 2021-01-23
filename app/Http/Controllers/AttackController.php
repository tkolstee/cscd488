<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attack;
use App\Models\Team;
use App\Exceptions\AttackNotFoundException;
use Exception;
use Auth;

class AttackController extends Controller
{
    public function page($page, Request $request) {
        switch($page){
            case 'sqlinjection': return $this->sqlInjection($request); break;
            case 'synflood': return $this->synFlood($request); break;
            case 'malvertise': return $this->malvertise($request); break;
            default: return (new RedTeamController)->home(); break;
        }
    }

    public function choosePayload($attack, $attMsg) {
        $payloads = $attack->payloads;
        if (empty($payloads)) {
            return $this->attackComplete($attack, $attMsg);
        }
        $redteam = Auth::user()->getRedTeam();
        return view('redteam.choosePayload')->with(compact('redteam','attack', 'attMsg'));
    }

    public function executePayload($request) {
        $attack = Attack::find($request->attID);
        $payload = $request->result;
        //Put the stuff for payload handling here? For now, just pass stuff to complete...
        return $this->attackComplete($attack, $request->attMsg);
    }

    public static function attackComplete($attack, $attMsg){
        $redteam = Team::find($attack->redteam);
        $attack->onAttackComplete();
        if($attack->success){
            $attMsg .= " You earned $".$attack->red_gain;
        }
        return (new RedTeamController)->home()->with(compact('attMsg'));
    }

    public function malvertise(request $request){
        $attack = Attack::find($request->attID);
        if($attack == null) throw new Exception($request->attID);//AttackNotFoundException();
        $blueteam = Team::find($attack->blueteam);
        $success = false;
        $attMsg = "";
        $type = substr($request->result, 0, strlen($request->result) - 1);
        $val = substr($request->result, -1);
        if($val == 1){
            $success = true;
            switch($type){
                case("drivebydownload"): $attMsg = "Someone viewed your ad and malware was successfully installed."; break;
                case("forceredirect"): $attMsg = "Someone got redirected and the malicious website was successful."; break;
                case("maliciousjavascript"): $attMsg = "The javascript executed successfully and someone viewed the malicious content."; break;
                case("executemalware"): $attMsg = "Someone clicked on the ad and the code successfully installed malware."; break;
                case("redirect"): $attMsg = "Someone clicked on the ad and was successfully redirected to the malicious website."; break;
            }
        }else{
            $success = false;
            switch($type){
                case("drivebydownload"): $attMsg = "Everyone who viewed the ad had anti-virus software to prevent the download."; break;
                case("forceredirect"): $attMsg = "Everyone who viewed the ad had anti-virus software to prevent the redirect."; break;
                case("maliciousjavascript"): $attMsg = "Everyone who viewed the ad had anti-virus software to prevent the javascript from executing."; break;
                case("executemalware"): $attMsg = "Everyone who clicked on the ad had anti-virus software to prevent the download."; break;
                case("redirect"): $attMsg = "No one clicked on your ad."; break;
            }
        }
        $attack->setSuccess($success);
        
        if ($success) {
            return $this->choosePayload($attack, $attMsg);
        }else {
            return $this->attackComplete($attack, $attMsg);
        }
    }

    public function synFlood(request $request){
        $attack = Attack::find($request->attID);
        if($attack == null) throw new Exception($request->attID);//AttackNotFoundException();
        $blueteam = Team::find($attack->blueteam);
        if($request->result1 == 1 && $request->result2 == 1) {
            $success = true;
            $attMsg = "You have successfully flooded ".$blueteam->name."'s backlog queue and created a denial-of-service.";
        }
        else {
            $success = false;
            $attMsg = "You have failed in your attempt to SYN flood ".$blueteam->name;
        }
        $attack->setSuccess($success);
        
        if ($success) {
            return $this->choosePayload($attack, $attMsg);
        }else {
            return $this->attackComplete($attack, $attMsg);
        }
    }

    public function sqlInjection(request $request){
        $attack = Attack::find($request->attID);
        if($attack == null) throw new AttackNotFoundException();
        $blueteam = Team::find($attack->blueteam);
        $url = $request->url;
        $success = false;
        $attMsg = "There was an application error and you were unsuccessful.";
        switch($attack->difficulty){
            case 1: $success = true; 
                $attMsg = "There were no security measures so you were successful."; break;
            case 2: if($url == "'") $success = true;
                $attMsg = "There was a SQL error, so you were successful in testing if ".$blueteam->name." is vulnerable to SQL injection."; break;
            case 3: if($url == "'--") $success = true;
                $attMsg = "You were successful in getting past user validation."; break;
            case 4: if($url == "' or 1=1--") $success = true;
                $attMsg = "You were successful in getting past user validation and you see all products."; break;
            default: break;
        }
        $attack->setSuccess($success);

        if ($success) {
            return $this->choosePayload($attack, $attMsg);
        }else {
            return $this->attackComplete($attack, $attMsg);
        }
    }
    
}
