<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attack;
use App\Models\Team;
use App\Exceptions\AttackNotFoundException;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AttackController extends Controller
{
    public function page($page, Request $request) {
        switch($page){
            case 'sqlinjection': return $this->sqlInjection($request); break;
            case 'sqlinjectioncheck': return $this->sqlInjectionCheckAnswer($request); break;
            case 'synflood': return $this->synFlood($request); break;
            case 'malvertise': return $this->malvertise($request); break;
            default: return (new RedTeamController)->home(); break;
        }
    }

    public static function attackComplete($attack, $attMsg){
        $redteam = Team::find($attack->redteam);
        $attack->onAttackComplete();
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
        
        return $this->attackComplete($attack, $attMsg);
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
        
        return $this->attackComplete($attack, $attMsg);
    }

    public function sqlInjection(request $request){
        $attack = Attack::find($request->attID);
        if($attack == null) throw new AttackNotFoundException();
        $url = $request->url;
        $this->sqlSetUp();

        $result = "";
        try {
            $result = DB::connection('sql_minigame')->select(DB::raw("SELECT * FROM users WHERE username = '$url'"));
        }
        catch (QueryException $e) {
            $result = "You caused a query error!";
            if ($attack->calculated_difficulty <= 1) {
                $attMsg = $result;
                $attack->setSuccess(true);
                return $this->attackComplete($attack, $attMsg);
            }
        }
        $redteam = Team::find($attack->redteam);
        $blueteam = Team::find($attack->blueteam);
        return view('minigame.sqlinjection')->with(compact('attack', 'blueteam', 'redteam', 'result'));
    }

    public function sqlInjectionCheckAnswer($request){
        $attack = Attack::find($request->attID);
        $passIn = $request->pass;
        $adminPass = DB::connection('sql_minigame')->table('users')->where('username', 'admin')->first()->password;

        $success = false;
        $attMsg = "You did not guess the admin's password correctly.";
        if ($passIn == $adminPass) {
            $success = true;
            $attMsg = "You successfully discovered the admin's password!";
        }
        $attack->setSuccess($success);
        return $this->attackComplete($attack, $attMsg);
    }

    public function sqlSetUp(){
        $connect = 'sql_minigame';
        Schema::connection($connect)->dropIfExists('users');
        Schema::connection($connect)->dropIfExists('products');

        Schema::connection($connect)->create('users', function($table){
            $table->increments('id');
            $table->text('username');
            $table->text('password');
        });
        Schema::connection($connect)->create('products', function($table){
            $table->increments('id');
            $table->text('product_name');
        });
        
        DB::connection($connect)->table('users')->insert([
            'username' => 'admin',
            'password' => generateRandomString(),
        ]);
        DB::connection($connect)->table('users')->insert([
            'username' => 'user',
            'password' => generateRandomString(),
        ]);
        DB::connection($connect)->table('products')->insert([
            'product_name' => 'product1',
        ]);
    }
}
