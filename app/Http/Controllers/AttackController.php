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

    public function sqlResultToTable($r, $one_row=false) {
        if ( $r->numColumns() == 0 || $r->columnType(0) == SQLITE3_NULL ) {
            return "No results found.";
        }
        $retval = '<table class="table table-bordered"><tr>';
        $rowcount = 0;

        foreach (range(0,$r->numColumns()-1) as $x) {
            $retval .= "<th>" . $r->columnName($x) . "</th>";
        }
        $retval .= "</tr>";

        while ($row = $r->fetchArray(SQLITE3_NUM)) {
            $rowcount++;
            if ($rowcount < 1 or !$one_row) {
                $retval .= "  <tr>";
                foreach ($row as $field) {
                    $retval .= "<td>" . $field . "</td>";
                }
                $retval .= "</tr>";
            }
        }

        $retval .= "</table>\n";

        if ($rowcount==0) { return "No results found."; }
        return $retval;
    }

    public function sqlInjection(request $request){

        $attack = Attack::find($request->attID);
        if($attack == null) throw new AttackNotFoundException();

        $blueteam   = Team::find($attack->blueteam);
        $redteam    = Team::find($attack->redteam);
        $difficulty = 1; #$attack->getDifficulty();
        $session    = $request->session();
        $success    = false;
        $result     = "";
        $dbh        = $this->sqlOpen($request);

        $targetuser = $session->get('target_username');
        $targetpass = $session->get('target_pass');

        $objective       = ($difficulty < 3) ? "Find all usernames" : "Get password for user ${targetuser}";
        $answer          = ($difficulty < 3) ? $targetuser : $targetpass;
        $onerow          = ($difficulty > 2);
        $filter_keywords = ($difficulty % 3 == 2);
        $quote           = ($difficulty == 5);
        $report_err      = ($difficulty % 3 < 2);
        $report_query    = ($difficulty % 3 == 0);

        $session->put('magic_word', $answer);

        if ( $request->has('outcome') ) {
            $success = ($request->outcome == $answer);
            $attack->setSuccess($success);
            $dbh->close();
            $this->sqlTearDown($request);
            $attmsg = $success ? 'You did it!' : 'You gave up.';
            return $this->attackComplete($attack, $attmsg);
        }

        if ( $request->username != NULL ) {
            $user = $request->username;
            if ($filter_keywords) {
                $user = preg_replace('/(AND|OR|NOT)/i', "xxx", $user);
            }
            if ($quote) {
                $user = SQLite3::escapeString($user);
            }
            $query = "SELECT username, phone FROM USERS WHERE username = '${user}';";

            try {
                $dbresult = $dbh->query($query);
                if (! $dbresult) { throw new Exception("database error"); }
                $result = $this->sqlResultToTable($dbresult, $onerow);
            } catch (exception $e) {
                $result = "A database error occurred.<br>";
                if ( $report_err < 1 ) {
                    $result .= "Error message:<br>" . $dbh->lastErrorMsg() . "<br>";
                }
                if ( $report_query < 2 ) {
                    $result .= "The query was:<br>${query}<br>";
                }
            }
        }

        if (strpos($result, $answer) !== false) {
            $success = true;
        }

        return view('minigame.sqlinjection')->with(compact('attack', 'blueteam', 'redteam', 'result', 'success', 'objective'));
    }

    public function sqlTearDown(Request $request) {

        $session = $request->session();
        $db = $session->pull('gamedb', '/nonexistent');


        if (file_exists($db)) { unlink($db); }
        $session->pull('target_username', '');
        $session->pull('target_pass', '');
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
                $request->session()->put('target_username', $user);
                $request->session()->put('target_pass', $pass);
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
        return new SQLite3($dbfile, SQLITE3_OPEN_READWRITE);
    }

    public function sqlDone(Request $request) {
        $dbfile = $request->session()->get('gamedb', NULL);
        if (! is_null($dbfile) && file_exists($dbfile)) {
            unlink($dbfile);
            $request->session()->forget('gamedb');
        }
    }

}
