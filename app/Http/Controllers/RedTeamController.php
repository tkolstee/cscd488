<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Asset;
use App\Models\Inventory;
use App\Models\Attack;
use Auth;
use App\Exceptions\AssetNotFoundException;
use App\Exceptions\AttackNotFoundException;
use App\Exceptions\TeamNotFoundException;
use App\Models\AttackLog;
use Error;
use Exception;

use App\Models\Attacks\SQLInjectionAttack;

class RedTeamController extends Controller {

    public function page($page, Request $request) {
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
            case 'store': return $this->store();break;
            case 'filter': return $this->filter($request);break;
            case 'status': return view('redteam.status')->with('redteam',$redteam); break;
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

    public function minigameComplete(request $request){
        $attack = Attack::find($request->attID);
        if($attack == null){
            throw new AttackNotFoundException();
        }
        $attMsg = "Success: ";
        if($request->result == 1){
            $attack->setSuccess(true);
            $attMsg .= "true";
        }else{
            $attack->setSuccess(false);
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
        
        //if the view doesn't exist return default
        return view('redteam.minigame')->with(compact('attack','redteam','blueteam'));
    }

    public function savePayload(request $request) {
        $attack = Attack::find($request->attID);
        $attack->payload_choice = $request->result;
        Attack::updateAttack($attack);
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
        if($request->result == ""){
            $error = "No-Team-Selected";
            return $this->startAttack()->with(compact('error'));
        }
        $user = Auth::user();
        $redteam = Auth::user()->getRedTeam();
        $blueteam = Team::get($request->result);
        $possibleAttacks = Attack::getAll();
        return view('redteam.chooseAttack')->with(compact('redteam','blueteam','possibleAttacks'));
    }

    public function startAttack(){
        try{
            $targets = Team::getBlueTeams()->where('id','!=',Auth::user()->blueteam);
        }catch(TeamNotFoundException $e){
            $targets = [];
        }
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
        $invIds = $request->input('results');
        if($invIds == null){
            $error = "no-asset-selected";
            return $this->inventory()->with(compact('error'));
        }
        $redteam = Auth::user()->getRedTeam();
        foreach($invIds as $invId){
            $inv = Inventory::find($invId);
            $success = $redteam->sellInventory($inv);
            if (!$success) {
                $error = "not-enough-owned";
                return $this->inventory()->with(compact('error'));
            }
        }
        return $this->inventory();
    }//end sell

    public function buy(request $request){
        $assetNames = $request->input('results');
        if($assetNames == null){
            $error = "no-asset-selected";
            return $this->store()->with(compact('error'));
        }
        $redteam = Auth::user()->getRedTeam();
        $totalCost = 0;
        //check total price
        foreach($assetNames as $assetName){
            $asset = Asset::get($assetName);
            $totalCost += $asset->purchase_cost;
        }
        if($redteam->balance < $totalCost){
            $error = "not-enough-money";
            return $this->store()->with(compact('error'));
        }
        //buy if you have enough
        foreach($assetNames as $assetName){
            $asset = Asset::get($assetName);
            $redteam->buyAsset($asset);
        }
        return $this->store();
    }

    public function store(){
        $redteam = Auth::user()->getRedTeam();
        $assets = Asset::getBuyableRed();
        $tags = Asset::getTags($assets);
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
            $tagFilter = $request->filter;
            $assets = Asset::filterByTag($assets, $tagFilter);
        }
        if (!empty($request->sort)) {
            $sort = $request->sort;
            $assets = $assets->sortBy($sort);
        }
        return view('redteam.store')->with(compact('redteam', 'assets', 'tags', 'ownedAssets'));
    }

    public function inventory(){
        $redteam = Auth::user()->getRedTeam();
        $inventory = $redteam->inventories();
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
