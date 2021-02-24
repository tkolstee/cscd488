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
use Illuminate\Support\Facades\Storage;
use SQLite3;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

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

    public function sqlResultToTable($r) {
        if ( $r->numColumns() == 0 || $r->columnType(0) == SQLITE3_NULL ) {
            return "No results found.";
        }
        $retval = '<table border=2\n  <tr>';
        $rowcount = 0;

        foreach (range(0,$r->numColumns()) as $x) {
            $retval .= "<th>" . $r->columnName($x) . "</th>";
        }
        $retval .= "</tr>\n";

        while ($row = $r->fetchArray(SQLITE3_NUM)) {
            $rowcount++;
            $retval .= "  <tr>";
            foreach ($row as $field) {
                $retval .= "<td>" . $field . "</td>";
            }
            $retval .= "</tr>\n";
        }

        $retval .= "</table>\n";

        if ($rowcount==0) { return "No results found."; }
        return $retval;
    }

    public function sqlInjection(request $request){
        $attack = Attack::find($request->attID);
        if($attack == null) throw new AttackNotFoundException();
        $blueteam = Team::find($attack->blueteam);
        $redteam  = Team::find($attack->redteam);
        $success  = false;

        if ( $request->resign == 'xxx' ) {
            $attack->setSuccess(false);
            return $this->attackComplete($attack, "You gave up.");
        }

        $url = $request->url;
        $dbh = $this->sqlOpen($request);
        $answer = $request->session()->get('target_answer');

        if ( $request->username != NULL ) {
            $user = $request->username;
            $query = "SELECT username, phone FROM USERS WHERE username = '${user}';";
            try {
                $dbresult = $dbh->query($query);
                if (! $dbresult) { throw new Exception("database error"); }
                $result = $this->sqlResultToTable($dbresult);
            } catch (exception $e) {
                $result = "A database error occurred:<br>\n" . $dbh->lastErrorMsg();
            }
        }

        if (strpos($result, $answer) !== false) {
            $success = true;
            $attack->setSuccess(true);
        }
        return view('minigame.sqlinjection')->with(compact('attack', 'blueteam', 'redteam', 'result', 'success'));
    }

    public function sqlSetUp(Request $request){
        $dbfile = Storage::path(Str::uuid().'.db');
        $request->session()->put('gamedb', $dbfile);
        $dbh = new SQLite3($dbfile);

        $faker = Faker::create();
        $dbh->exec("create table users (id INTEGER, username VARCHAR(30), password VARCHAR(30), phone VARCHAR(13));");

        $target = rand(0, 20);
        foreach (range(0, 20) as $x) {
            $user  = $faker->userName();
            $pass  = $faker->password();
            $phone = $faker->phoneNumber();
            if ( $target == $x ) {
                $request->session()->put('target_question', "Get password for user '${user}'");
                $request->session()->put('target_answer', $pass);
            }
            $statement = $dbh->prepare('INSERT INTO users (id, username, password, phone) VALUES (:id, :user, :pass, :phone)');
            $statement->bindValue(':id', $x);
            $statement->bindValue(':user', $user);
            $statement->bindValue(':pass', $pass);
            $statement->bindValue(':phone', $phone);
            $statement->execute();
        }

        return $dbh;
    }

    public function sqlOpen(Request $request) {
        $dbfile = $request->session()->get('gamedb', NULL);
        if (is_null($dbfile) || ! file_exists($dbfile)) { return $this->sqlSetUp($request); }
        return new SQLite3($dbfile, SQLITE3_OPEN_READONLY);
    }

    public function sqlDone(Request $request) {
        $dbfile = $request->session()->get('gamedb', NULL);
        if (! is_null($dbfile) && file_exists($dbfile)) {
            unlink($dbfile);
            $request->session()->forget('gamedb');
        }
    }

}
