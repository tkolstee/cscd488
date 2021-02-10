<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Asset;
use App\Models\Blueteam;
use App\Models\Setting;
use App\Models\Inventory;
use App\Models\Game;
use Auth;
use App\Exceptions\AssetNotFoundException;
use App\Exceptions\TeamNotFoundException;
use App\Exceptions\InventoryNotFoundException;
use Exception;
use App\Models\Attack;
use App\Models\Bonus;
use Illuminate\Pagination\Paginator;

class BlueTeamController extends Controller {

    public function page($page, Request $request) {

        try{
            $blueteam = Team::find(Auth::user()->blueteam);
        }catch(TeamNotFoundException $e){
            $blueteam = null;
        }
        if($blueteam != null){
            //methods available while not on a turn
            if($page == 'startturn') return $this->startTurn(); //Testing Purposes
            if($page == 'settings') return $this->settings($request);
            if($page == 'changename') return $this->changeName($request); 
            if($page == 'leaveteam') return $this->leaveTeam($request);
            if($page == 'picktarget') return $this->pickTarget($request);
            if($page == 'changeleader') return $this->changeLeader($request);
            $team_id = Auth::user()->blueteam;
            $turn = Auth::user()->getTurnTaken();
            if($turn == 1){
                $endTime = Setting::get('turn_end_time');
                return $this->home()->with(compact('turn', 'endTime'));
            }
            //logged in with blueteam options
            switch ($page) {
                case 'home': return $this->home(); break;
                case 'broadcast': return $this->broadcast($request); break;
                case 'analyzeAttack': return $this->analyzeAttack($request); break;
                case 'clearNotifs': return $this->clearNotifs(); break;
                case 'news': return $this->news(); break;
                case 'attacks': return $this->attacks(); break;
                case 'planning': return view('blueteam.planning')->with('blueteam',$blueteam); break;
                case 'status': return $this->status(); break;
                case 'removeBonus': return $this->removeBonus($request); break;
                case 'store': return $this->store();
                case 'filter': return $this->filter($request);
                case 'training': return view('blueteam.training')->with('blueteam',$blueteam); break;
                case 'buy': return $this->buy($request); break;
                case 'inventory': return $this->inventory(); break;
                case 'upgrade': return $this->upgrade($request);break;
                case 'sell': return $this->sell($request); break;
                case 'leaderboard': return $this->leaderboard(); break;
                case 'endturn': return $this->endTurn(); break;
                case 'cancel': return $this->cancel($request); break;
                case 'removecartitem': return $this->removeCartItem($request); break;
                default: return $this->home(); break;
            }
        }
        //no blue team options
        switch ($page) {
            case 'home': return $this->home(); break;
            case 'create': return $this->create($request); break;
            case 'join': return $this->join($request); break;
            case 'joinmembers': return $this->joinMembers($request); break;
            default: return $this->home(); break;
        }
        
    }
 
    public static function removeSellItem($id){
        $removed = false;
        $sellCart = session('sellCart');
        $key = array_search($id, $sellCart ?? []);
        if(is_int($key)){
            unset($sellCart[$key]);
            $removed = true;
        }
        session(['sellCart' => $sellCart]);
        return $removed;
    }

    public function status(){
        $blueteam = Auth::user()->getBlueTeam();
        $bonuses = $blueteam->getBonusesByTarget();
        $bonuses = $bonuses->sortByDesc("created_at")->paginate(3);
        return view('blueteam/status')->with(compact('blueteam','bonuses'));
    }

    public function removeBonus(request $request){
        $bonus = Bonus::find($request->bonusID);
        $bonus->payToRemove();
        return $this->status();
    }

    public function leaveTeam(request $request){
        if($request->result == "stay"){
            return $this->settings($request);
        }
        else if($request->result == "leave"){
            $user = Auth::user();
            $user->leaveBlueTeam();
            return $this->home();
        }else{
            $error = "invalid-option";
            return $this->settings($request)->with(compact('error'));
        }
        
    }

    public function changeLeader(request $request){
        $user = Auth::user();
        $success = $user->changeLeader($request->result);
        if(!$success) $error = "user-not-on-team";
        else $error = null;
        return $this->settings($request)->with(compact('error'));
    }

    public function changeName(request $request){
        try{
            Team::get($request->name);
        }
        catch(TeamNotFoundException $e){
            $team = Auth::user()->getBlueTeam();
            $team->setName($request->name);
            return $this->settings($request);
        }
            $error = "name-taken";
            return $this->settings($request)->with(compact('error'));
    }

    public function settings(request $request){
        $changeName = false;
        $leaveTeam = false;
        $changeLeader = false;
        if($request->changeNameBtn == 1){
            $changeName = true;
        }
        if($request->leaveTeamBtn == 1){
            $leaveTeam = true;
        }
        if($request->changeLeaderBtn == 1){
            $changeLeader = true;
        }
        $blueteam = Auth::user()->getBlueTeam();
        $leader = $blueteam->leader();
        $members = $blueteam->members();
        $turn = Auth::user()->getTurnTaken();
        return view('blueteam/settings')->with(compact('blueteam','leader','members','changeName','leaveTeam', 'turn', 'changeLeader'));
    }

    public function startTurn(){ //Testing Purposes
        Auth::user()->setTurnTaken(0);
        $turn = 0;
        return $this->home()->with(compact('turn'));
    }

    public function cancel(Request $request){
        if(empty($_POST['cancel'])) return $this->store($request);
        $result = array_keys($_POST['cancel'])[0];
        $view = $this->store($request);
        if($request->cart == "buy"){
            $session = session('buyCart');
        }else{
            $session = session('sellCart');
            $view = $this->inventory($request);
        }
        $key = array_search($result, $session);
        unset($session[$key]);
        if($request->cart == "buy"){
            session(['buyCart' => $session]);
        }else{
            session(['sellCart' => $session]);
        }
        $error = null;
        return $view;
    }

    public function endTurn(){
        //Buy and sell items in Session
        $blueteam = Auth::user()->getBlueTeam();
        $totalBalance = 0;
        $sellCart = session('sellCart');
        if (!empty($sellCart)){
            foreach($sellCart as $sellItem){
                $inv = Inventory::find($sellItem);
                $success = $blueteam->sellInventory($inv);
                if (!$success) {
                    $error = "not-enough-owned";
                    $key = array_search($sellItem, $sellCart);
                    unset($sellCart[$key]);
                    session(['sellCart' => $sellCart]);
                    return $this->store()->with(compact('error'));
                }
                $key = array_search($sellItem, $sellCart);
                unset($sellCart[$key]);
            }
        }
        session(['sellCart' => null]);

        $buyCart = session('buyCart');
        $targeted = [];
        if (!empty($buyCart)){
            $totalCost = 0;
            foreach($buyCart as $assetName){
                $asset = Asset::getByName($assetName);
                $totalCost += $asset->purchase_cost;
            }
            if($totalCost > $blueteam->balance){
                if($blueteam->balance == 0){
                    $error = "no-money";
                    session(['buyCart' => null]);
                    //if balance 0 team didn't sell so it is okay to not end turn
                    return $this->home()->with(compact('error'));
                }
                $request = Request::create('/endturn','POST',[]);
                return $this->removeCartItem($request, $totalCost);
            }
            foreach($buyCart as $assetName){
                $asset = Asset::getByName($assetName);
                $success = $blueteam->buyAsset($asset);
                if (!$success){
                    $error = "not-enough-money";
                    $key = array_search($assetName, $buyCart);
                    unset($buyCart[$key]);
                    session(['buyCart' => $buyCart]);
                    return $this->store()->with(compact('error'));
                }
                if(in_array("Targeted", $asset->tags)){
                    $inv = $blueteam->inventory($asset, 1);
                    $targeted[] = $inv;
                }
                $key = array_search($assetName, $buyCart);
                unset($buyCart[$key]);
            }
        }
        session(['buyCart' => null]);
        //update turn stuff
        Auth::user()->setTurnTaken(1);
        if(count($targeted) > 0){
            try{
                $redteams = Team::getRedTeams();
                $endTurn = true;
                return view('blueteam.target')->with(compact('blueteam', 'targeted', 'redteams', 'endTurn'));
            }catch(TeamNotFoundException $e){}
        }
        $turn = 1;
        $endTime = Setting::get('turn_end_time');
        return $this->home()->with(compact('turn', 'endTime'));
    }
 
    public function removeCartItem(request $request,$totalCost = 0){
        $blueteam = Auth::user()->getBlueTeam();
        $results = $request->results;
        if(isset($results) && is_array($results)){
            $assetNames = $results;
        }
        if(empty($assetNames)){
            return view('blueteam.removecart')->with(compact('blueteam','totalCost'));
        }
        $buyCart = session('buyCart');
        foreach($assetNames as $assetName){
            $key = array_search($assetName, $buyCart ?? []);
            if(!is_int($key)){
                return $this->endTurn();
            }else{
                unset($buyCart[$key]);
            }
        }
        session(['buyCart' => $buyCart]);
        return $this->endTurn();
    }

    public function pickTarget(Request $request){
        $blueteam = Auth::user()->getBlueTeam();
        if(empty($request->invCount)){
            $inv = Inventory::find($request->submit);
            if($inv == null) throw new InventoryNotFoundException();
            $targeted = [$inv];
            $redteams = null;
            try{
                $redteams = Team::getRedTeams();
            }catch(TeamNotFoundException $e){}
            $currentPage = $request->currentPage;
            return view('blueteam.target')->with(compact('blueteam', 'targeted', 'redteams','currentPage'));
        }
        $count = $request->invCount;
        for($i = 1; $i < $count + 1; $i++){
            $result = "result".$i;
            $redteamId = $request->$result;
            if($redteamId != null){
                $redteam = Team::get($redteamId);
                $name = "name" . $count;
                $asset = Asset::get($request->$name);
                $inv = $blueteam->inventory($asset, 1);
                $inv->setInfo($redteam->name);
                $this->removeSellItem($inv->id);
            }
        }
        if($request->endTurn){
            $turn = 1;
            $endTime = Setting::get('turn_end_time');
            return $this->home()->with(compact('turn', 'endTime'));
        }
        return $this->inventory($request);
        
    }

    public function home(){
        try {
            $blueteam = Auth::user()->getBlueTeam();
        }
        catch (TeamNotFoundException $e) {
            return view('blueteam.home');
        }
        $leader = $blueteam->leader();
        $members = $blueteam->members();
        $turn = Auth::user()->getTurnTaken();
        $unreadAttacks = Attack::getUnreadDetectedAttacks($blueteam->id);
        return  view('blueteam.home')->with(compact('blueteam','leader','members', 'turn', 'unreadAttacks'));
    }

    public function broadcast(request $request) {
        $attID = $request->attID;
        $attack = Attack::find($attID);
        $attack->setNews(true);
        $attack->setNotified(true);
        return $this->home();
    }

    public function analyzeAttack(request $request) {
        $attID = $request->attID;
        $attack = Attack::find($attID);
        $attack->analyze();
        return $this->attacks();
    }

    public function clearNotifs() {
        $blueteam = Auth::user()->getBlueTeam();
        $unreadAttacks = Attack::getUnreadDetectedAttacks($blueteam->id);
        foreach ($unreadAttacks as $attack) {
            $attack->setNotified(true);
        }
        return $this->home();
    }

    public function news(){
        try {
            $blueteam = Auth::user()->getBlueTeam();
        }
        catch (TeamNotFoundException $e) {
            return view('blueteam.home');
        }
        $news = Attack::getNews()->sortByDesc('created_at')->paginate(5);
        return view('blueteam.news')->with(compact('blueteam', 'news'));
    }

    public function attacks() {
        $blueteam = Auth::user()->getBlueTeam();
        $previousAttacks = Attack::getBluePreviousAttacks($blueteam->id)->sortByDesc('created_at')->paginate(4);
        return view('blueteam.attacks')->with(compact('blueteam', 'previousAttacks'));
    }

    public function upgrade(request $request){
        $result = $request->submit;
        $inv = Inventory::find($result);
        if($inv == null) throw new InventoryNotFoundException();
        $this->removeSellItem($inv->id);
        $success = $inv->upgrade();
        if($success == false) $error = "unsuccessful";
        else $error = null;
        return $this->inventory($request)->with(compact('error'));
    }

    public function sell(request $request){
        $invIds = $request->input('results');
        if($invIds == null){
            $error = "no-asset-selected";
            return $this->inventory()->with(compact('error'));
        }
        $sellCart = session('sellCart');
        foreach($invIds as $inv){
            if(Inventory::find($inv) == null) {
                throw new InventoryNotFoundException();
            }
            $sellCart[] = $inv;
        }
        session(['sellCart' => $sellCart]);
        return $this->inventory($request);
    }//end sell

    public function inventory(request $request = null){
        if($request != null){
            $currentPage = $request->currentPage;
            if(!empty($currentPage)){
                Paginator::currentPageResolver(function () use ($currentPage) {
                    return $currentPage;
                });
            }
        }
        $blueteam = Auth::user()->getBlueTeam();
        $inventory = $blueteam->inventories()->paginate(5);
        $inventory->setPath('/blueteam/inventory');
        return view('blueteam.inventory')->with(compact('blueteam', 'inventory'));
    }

    public function buy(request $request){
        $assetNames = $request->input('results');
        $blueteam = Auth::user()->getBlueTeam();
        if($assetNames == null){
            $error = "no-asset-selected";
            return $this->store()->with(compact('error'));
        }
        $blueteam->balance = 1000; $blueteam->update(); //DELETE THIS IS FOR TESTING PURPOSES
        $buyCart = session('buyCart');
        foreach($assetNames as $asset){
            $actAsset = Asset::get($asset);
            $buyCart[] = $actAsset->name;
        }
        session(['buyCart' => $buyCart]);
        $currentPage = $request->currentPage;
        return $this->store($request);
    }

    public function store(request $request = null){
        if($request != null){
            $currentPage = $request->currentPage;
            if(!empty($currentPage)){
                Paginator::currentPageResolver(function () use ($currentPage) {
                    return $currentPage;
                });
            }
        }
        session(['blueFilter' => null]);
        session(['blueSort' => null]);
        $blueteam = Auth::user()->getBlueTeam();
        $assets = collect(Asset::getBuyableBlue());
        $tags = Asset::getTags($assets);
        $assets = $assets->paginate(5);
        $assets->setPath('/blueteam/store');
        $ownedAssets = $blueteam->assets();
        return view('blueteam.store')->with(compact('blueteam', 'assets', 'tags', 'ownedAssets'));
    }

    public function filter(request $request){
        $blueAssets = Asset::getBuyableBlue();
        $tags = Asset::getTags($blueAssets);
        $assets = collect($blueAssets);
        $blueteam = Auth::user()->getBlueTeam();
        $ownedAssets = $blueteam->assets();
        if (!empty($request->filter)) {
            if($request->filter == "No Filter"){
                session(['blueFilter' => null]);
            }else{
                session(['blueFilter' => $request->filter]);
            }
        }
        if(!empty(session('blueFilter'))){
            $tagFilter = session('blueFilter');
            $assets = Asset::filterByTag($assets, $tagFilter);
        }
        if (!empty($request->sort)) {
            if($request->sort != "Name"){
                session(['blueSort' => $request->sort]);
            }else{
                session(['blueSort' => null]);
            }
        }
        if(!empty(session('blueSort'))){
            $sort = session('blueSort');
            $assets = $assets->sortBy($sort);
        }
        $assets = $assets->paginate(5);
        $assets->setPath('/blueteam/filter');
        return view('blueteam.store')->with(compact('blueteam', 'assets', 'tags', 'ownedAssets'));
    }

    public function leaderboard() {
        $blueteam = Auth::user()->getBlueTeam();
        $teams = Team::getBlueTeams()->sortByDesc('reputation')->paginate(5);
        return view('blueteam.leaderboard')->with(compact('blueteam', 'teams'));
    }

    public function joinMembers(request $request){
        $teamName = $request->submit;
        $team = Team::get($teamName);
        $viewMembers = $team->name;
        $viewTeamLeader = $team->leader();
        $viewTeamMembers = $team->members();
        $request->result = "";
        return $this->join($request)->with(compact('viewMembers','viewTeamLeader','viewTeamMembers'));
    }

    public function join(request $request){
        if($request->result == ""){
            try{
                $blueteams = Team::getBlueTeams();
            }catch(TeamNotFoundException $e){
                $blueteams = [];
            }
            return view('blueteam.join')->with(compact('blueteams'));
        }
        Auth::user()->joinBlueTeam($request->result);
        return $this->home();
    }

    public function create(request $request){
        if($request->name == "") return view('blueteam.create'); 
        $this->validate($request, [
            'name' => ['required', 'unique:teams', 'string', 'max:255'],
        ]);
        Auth::user()->createBlueTeam($request->name);
        return $this->home();
    }
}
