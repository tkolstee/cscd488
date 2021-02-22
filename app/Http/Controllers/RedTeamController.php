<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Asset;
use App\Models\Inventory;
use App\Models\Attack;
use App\Models\Payload;
use App\Models\Trade;
use Auth;
use App\Exceptions\AttackNotFoundException;
use App\Exceptions\TeamNotFoundException;
use Illuminate\Pagination\Paginator;

class RedTeamController extends Controller {

    public function page($page, Request $request) {
        if($page != 'chooseattack'){
            session(['redCurrentTarget' => null]);
        }
        try{
            $redteam = Auth::user()->getRedTeam();
        }catch(TeamNotFoundException $e){
            switch ($page) {
                case 'home': return $this->home(); break;
                case 'create': return $this->create($request); break;
                case 'learn': return (new LearnController)->page($page, $request); break;
                default: return $this->home(); break;
            }
        }
        switch ($page) {
            case 'home': return $this->home(); break;
            case 'attacks': return $this->attacks(); break;
            case 'learn': return (new LearnController)->page($page, $request); break;
            case 'store': return $this->store($request);break;
            case 'market': return $this->market($request);break;
            case 'createtrade': return $this->createTrade($request);break;
            case 'filter': return $this->filter($request);break;
            case 'status': return $this->status($request); break;
            case 'buy': return $this->buy($request); break;
            case 'inventory': return $this->inventory(); break;
            case 'upgrade': return $this->upgrade($request); break;
            case 'sell': return $this->sell($request); break;
            case 'startattack': return $this->startAttack(); break;
            case 'chooseattack': return $this->chooseAttack($request); break;
            case 'performattack': return $this->performAttack($request); break;
            case 'savePayload': return $this->savePayload($request); break;
            case 'attackhandler': return $this->attackHandler($request); break;
            case 'settings': return $this->settings($request); break;
            case 'changename': return $this->changeName($request); break;
            case 'leaveteam': return $this->leaveTeam($request); break;
            case 'minigamecomplete': return $this->minigameComplete($request); break;
            default: return $this->home(); break;
        }
    }

    public function createTrade(request $request){
        $redteam = Auth::user()->getRedTeam();
        if(empty($request->inv_id) || empty($request->price)){
            $inventories = $redteam->tradeableInventories();
            return view('redteam.createtrade')->with(compact('inventories','redteam'));
        }
        $trade = $redteam->createTrade($request->inv_id, $request->price);
        if($trade == false){
            $error = "Trade could not be created";
            $inventories = $redteam->tradeableInventories();
            return view('redteam.createtrade')->with(compact('inventories','redteam','error'));
        }
        return $this->market($request);
    }

    public function market(request $request){
        $redteam = Auth::user()->getRedTeam();
        if(empty($request->tradeId)){
            $currentTrades = Trade::getCurrentRedTrades()->paginate(5);
            return view('redteam.market')->with(compact('redteam','currentTrades'));
        }else{
            $trade = $redteam->completeTrade($request->tradeId);
            $currentTrades = Trade::getCurrentRedTrades()->paginate(5);
            if($trade == false){
                $error = "Trade Not Completed";
                return view('redteam.market')->with(compact('redteam','currentTrades','error'));
            }
            return view('redteam.market')->with(compact('redteam','currentTrades'));
        }
    }

    public function status(request $request){
        $redteam = Team::find(Auth::user()->redteam);
        $bonuses = $redteam->getBonuses();
        $bonuses = $bonuses->sortBy("target_id")->paginate(3);
        return view('redteam/status')->with(compact('redteam','bonuses'));
    }

    public function leaveTeam(request $request){
        if($request->result == "stay"){
            return $this->settings($request);
        }
        else if($request->result != "leave"){
            $error = "invalid-option";
            return $this->settings($request)->with(compact('error'));
        }
        Auth::user()->leaveRedTeam();
        return $this->home();
    }

    public function changeName(request $request){
        try{
            Team::get($request->name);
        }catch(TeamNotFoundException $e){
            $team = Auth::user()->getRedTeam();
            $team->setName($request->name);
        }
        $error = "name-taken";
        return $this->settings($request)->with(compact('error'));
    }

    public function settings($request){
        $changeName = false;
        $leaveTeam = false;
        if($request->changeNameBtn == 1){
            $changeName = true;
        }
        if($request->leaveTeamBtn == 1){
            $leaveTeam = true;
        }
        $redteam = Auth::user()->getRedTeam();
        return view('redteam/settings')->with(compact('redteam','changeName','leaveTeam'));
    }

    public function home(){
        try{
            $redteam = Auth::user()->getRedTeam();
        }catch(TeamNotFoundException $e){
            $redteam = null;
        }
        return view('redteam.home')->with(compact('redteam'));
    }

    public function attacks(){
        $redteam = Auth::user()->getRedTeam();
        $previousAttacks = Attack::getRedPreviousAttacks($redteam->id)->sortByDesc('created_at')->paginate(4);
        return view('redteam.attacks')->with(compact('redteam','previousAttacks')); 
    }

    public function minigameComplete($attack){
        if($attack == null){
            throw new AttackNotFoundException();
        }
        $attMsg = "Success: ";
        if($attack->success){
            $attMsg .= "true";
        }else{
            $attMsg .= "false";
        }
        $attack->onAttackComplete();
        return $this->home()->with(compact('attMsg'));
    }

    public function minigameStart($attack){
        $redteam = Team::find($attack->redteam);
        $blueteam = Team::find($attack->blueteam);
        //find the minigame for that attack, then return different view
        $dir = opendir(dirname(__FILE__)."/../../../resources/views/minigame");
        while(($view = readdir($dir)) !== false){
            if($view != "." && $view != ".."){
                $length = strlen($view);
                $view = substr($view, 0, $length - 10);
                if(strtolower($attack->class_name) == $view){
                    return view('minigame.'.$view)->with(compact('attack','redteam','blueteam'));
                }
            }
        }
        
        //if the view doesn't exist return by chance
        $rand = rand(0,500)/100;
        if($rand >= $attack->calculated_difficulty){
            $attack->setSuccess(true);
        }else{
            $attack->setSuccess(false);
        }
        return $this->minigameComplete($attack);
    }

    public function savePayload(request $request) {
        $attack = Attack::find($request->attID);
        $attack->payload_choice = $request->result;
        Attack::updateAttack($attack);
        $payload = Payload::get($attack->payload_choice);
        $payload->onPreAttack($attack);
        return $this->minigameStart($attack);
    }

    public function choosePayload($attack){
        if(!$attack->possible){
            $attMsg = $attack->errormsg;
            return $this->home()->with(compact('attMsg'));
        }
        $payloads = $attack->getPayloads();
        if (empty($payloads)) {
            return $this->minigameStart($attack);
        }
        $redteam = Auth::user()->getRedTeam();
        return view('redteam.choosePayload')->with(compact('redteam','attack', 'payloads'));
    }

    public function performAttack(request $request){
        if($request->result == ""){
            $error = "No-Attack-Selected";
            return $this->chooseAttack($request)->with(compact('error'));
        }
        $redteam = Auth::user()->getRedTeam();
        $blueteam = Team::get($request->blueteam);
        $attack = Attack::create($request->result, $redteam->id, $blueteam->id);
        $attack->onPreAttack();
        return $this->choosePayload($attack);
    }

    public function chooseAttack(request $request){
        if(empty(session('redCurrentTarget'))){
            if($request->result == ""){
                $error = "No-Team-Selected";
                return $this->startAttack()->with(compact('error'));
            }else{
                session(['redCurrentTarget' => $request->result]);
            }
        }
        $user = Auth::user();
        $redteam = Auth::user()->getRedTeam();
        $blueteam = Team::get(session('redCurrentTarget'));
        $possibleAttacks = collect(Attack::getAll())->paginate(5);
        $possibleAttacks->setPath('/redteam/chooseattack');
        return view('redteam.chooseAttack')->with(compact('redteam','blueteam','possibleAttacks'));
    }

    public function startAttack(){
        try{
            $targets = Team::getBlueTeams()->where('id','!=',Auth::user()->blueteam);
        }catch(TeamNotFoundException $e){
            $targets = [];
            $targets = collect($targets);
        }
        $targets = $targets->paginate(5);
        $targets->setPath('/redteam/startattack');
        $redteam = Auth::user()->getRedTeam();
        return view('redteam.startAttack')->with(compact('targets','redteam'));
    }

    public function upgrade(request $request){
        $result = $request->submit;
        $asset = Asset::get(substr($result, 0, strlen($result)-1));
        $level = substr($result, -1);
        $success = Auth::user()->getRedTeam()->inventory($asset, $level)->upgrade();
        if($success == false) $error = "unsuccessful";
        else $error = null;
        return $this->inventory()->with(compact('error'));
    }

    public function sell(request $request){
        $results = $request->results;
        $redteam = Auth::user()->getRedTeam();
        if(count($results) == 0){
            $error = "no-asset-selected";
            return $this->inventory()->with(compact('error'));
        }
        foreach($results as $invId=>$quantity){//buy the items
            $inv = Inventory::find($invId);
            for($i = 0; $i < $quantity; $i++){
                $success = $redteam->sellInventory($inv);
                if(!$success){
                    $error = "not-enough-owned";
                    return $this->inventory()->with(compact('error'));
                }
            }
        }
        return $this->inventory($request);
    }//end sell

    public function buy(request $request){
        $results = $request->results;
        $redteam = Auth::user()->getRedTeam();
        if(count($results) == 0){
            $error = "no-asset-selected";
            return $this->store()->with(compact('error'));
        }
        $totalCost = 0;
        foreach($results as $assetName=>$quantity){//check total price first
            $actAsset = Asset::get($assetName);
            $totalCost += $actAsset->purchase_cost * $quantity;
        }
        if($redteam->balance < $totalCost){
            $error = "not-enough-money";
            return $this->store()->with(compact('error'));
        }
        foreach($results as $assetName=>$quantity){//buy the items
            $actAsset = Asset::get($assetName);
            for($i = 0; $i < $quantity; $i++){
                $redteam->buyAsset($actAsset);
            }
        }
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
        session(['redFilter' => null]);
        session(['redSort' => null]);
        $redteam = Auth::user()->getRedTeam();
        $assets = Asset::getBuyableRed();
        $tags = Asset::getTags($assets);
        $assets = collect($assets)->paginate(5);
        $assets->setPath('/redteam/store');
        $ownedAssets = $redteam->assets();
        return view('redteam.store')->with(compact('redteam', 'assets', 'tags', 'ownedAssets'));
    }

    public function filter(request $request){
        $redAssets = Asset::getBuyableRed();
        $tags = Asset::getTags($redAssets);
        $redteam = Auth::user()->getRedTeam();
        $ownedAssets = $redteam->assets();
        $assets = collect($redAssets);
        if (!empty($request->filter)) {
            if($request->filter == "No Filter"){
                session(['redFilter' => null]);
            }else{
                session(['redFilter' => $request->filter]);
            }
        }
        if(!empty(session(['redFilter']))){
            $tagFilter = session('redFilter');
            $assets = Asset::filterByTag($assets, $tagFilter);
        }
        if (!empty($request->sort)) {
            if($request->sort == "Name"){
                session(['redSort' => null]);
            }else{
                session(['redSort' => $request->sort]);
            }
            }
        if(!empty(session('redSort'))){
            $sort = session('redSort');
            $assets = $assets->sortBy($sort);
        }
        $assets = $assets->paginate(5);
        $assets->setPath('/redteam/filter');
        return view('redteam.store')->with(compact('redteam', 'assets', 'tags', 'ownedAssets'));
    }

    public function inventory(request $request = null){
        if($request != null){
            $currentPage = $request->currentPage;
            if(!empty($currentPage)){
                Paginator::currentPageResolver(function () use ($currentPage) {
                    return $currentPage;
                });
            }
        }
        $redteam = Auth::user()->getRedTeam();
        $inventory = $redteam->inventories()->paginate(5);
        return view('redteam.inventory')->with(compact('redteam', 'inventory'));
    }

    public function create(request $request){
        if($request->name == ""){ return view('redteam.create');} 
        $request->validate([
            'name' => ['required', 'unique:teams', 'string', 'max:255'],
        ]);
        Auth::user()->createRedTeam($request->name);
        return $this->home();
    }
}
