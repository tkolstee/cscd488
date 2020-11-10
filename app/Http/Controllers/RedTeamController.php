<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Asset;
use Auth;
use View;
use Exception;

class RedTeamController extends Controller {

    public function page($page, Request $request) {
        $redteam = Team::find(Auth::user()->redteam);
        switch ($page) {
            case 'home': return view('redteam.home')->with('redteam',$redteam); break;
            case 'attacks': return view('redteam.attacks')->with('redteam',$redteam); break;
            case 'learn': return view('redteam.learn')->with('redteam',$redteam); break;
            case 'store': return $this->store();break;
            case 'status': return view('redteam.status')->with('redteam',$redteam); break;
            case 'create': return $this->create($request); break;
            case 'buy': return $this->buy($request);break;
        }
    }

    public function buy(request $request){

    }

    public function store(){
        $redteam = Team::find(Auth::user()->redteam);
        $assets = Asset::all()->where('blue', '=', 0)->where('buyable', '=', 1);
        return view('redteam.store')->with(compact('redteam', 'assets'));
    }

    public function create(request $request){
        if($request->name == "") return view('redteam.create'); 
        $request->validate([
            'name' => ['required', 'unique:teams', 'string', 'max:255'],
        ]);
        $redteam = new Team();
        $redteam->name = $request->name;
        $redteam->balance = 0;
        $redteam->blue = 0;
        $redteam->reputation = 0;
        $redteam->save();
        $user = Auth::user();
        $user->redteam = substr(Team::all()->where('name', '=', $request->name)->pluck('id'), 1, 1);
        $user->update();
        return view('redteam.home')->with('redteam',$redteam);
    }

    public function delete(request $request){
        $team = Team::all()->where('name', '=', $request->name);
        if($team->isEmpty()) {
            throw new Exception("TeamDoesNotExist");
        }
        $id = substr($team->pluck('id'), 1, 1);
        Team::destroy($id);
        return view('home');
    }
}
