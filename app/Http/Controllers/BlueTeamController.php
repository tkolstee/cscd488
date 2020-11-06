<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Asset;
use View;
use Auth;

class BlueTeamController extends Controller {

    public function page($page, Request $request) {

        $blueteam = Team::find(Auth::user()->blueteam);
        switch ($page) {
            case 'home': return view('blueteam.home')->with('blueteam',$blueteam); break;
            case 'planning': return view('blueteam.planning')->with('blueteam',$blueteam); break;
            case 'status': return view('blueteam.status')->with('blueteam',$blueteam); break;
            case 'store': return store();
            case 'training': return view('blueteam.training')->with('blueteam',$blueteam); break;
            case 'create': return $this->create($request); break;
            case 'join': return join($request); break;
            case 'buy': return buy($request); break;
        }

    }
 
    public function buy(request $request){
        $assets = $request->get('results');
        $totalCost = 0;
        foreach($assets as $asset){
            $totalCost += Asset::all()->where('name','=',$asset)->purchase_cost;
        }
        if(Team::find(Auth::user()->blueteam)->revenue < $totalCost)
            return ('blueteam.store')->with('not-enough-money',$error);
        foreach($assets as $asset){
            //add asset to inventory and charge user
        }
    }

    public function store(){
        $user = Auth::user();
        $blueid = $user->blueteam;
        $blueteam = Team::find($blueid);
        $assets = Asset::all()->where(['blue', '=', 1],['buyable','=',1]);
        return view('blueteam.store')->with('blueteam',$blueteam);
    }

    public function join(request $request){
        if($request->result == ""){
            $blueteams = Team::all()->where('blue', '=', 1);
            return view('blueteam.join')->with('blueteams', $blueteams);
        }
        $user = Auth::user();
        $blueteam = Team::all()->where('name', '=', $request->result);
        $user->blueteam = substr($blueteam->pluck('id'), 1, 1);
        $user->update();
        return view('blueteam.home')->with('blueteam',$blueteam);
    }

    public function create(request $request){
        if($request->name == "") return view('blueteam.create'); 
        $request->validate([
            'name' => ['required', 'unique:teams', 'string', 'max:255'],
        ]);
        $blueteam = new Team();
        $blueteam->name = $request->name;
        $blueteam->balance = 0;
        $blueteam->blue = 1;
        $blueteam->reputation = 0;
        $blueteam->save();
        $user = Auth::user();
        $user->blueteam = substr(Team::all()->where('name', '=', $request->name)->pluck('id'), 1, 1);
        $user->update();
        return view('blueteam.home')->with('blueteam',$blueteam);
    }

    public function delete(request $request){
        $team = Team::all()->where('name', '=', $request->name);
        $team->delete();
        return view('home');
    }

}
