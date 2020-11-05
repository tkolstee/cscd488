<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Team;
use View;
use Auth;

class BlueTeamController extends Controller {

    public function page($page, Request $request) {

        switch ($page) {
            case 'home': return view('blueteam.home'); break;
            case 'planning': return view('blueteam.planning'); break;
            case 'status': return view('blueteam.status'); break;
            case 'store': return view('blueteam.store'); break;
            case 'training': return view('blueteam.training'); break;
        }

    }

    public function join(request $request){
        if($request->result == ""){
            $blueteams = Team::all()->where('blue', '=', 1);
            return View::make('blueteam.join')->with('blueteams', $blueteams);
        }
        $user = Auth::user();
        $user->blueteam = substr(Team::all()->where('name', '=', $request->result)->pluck('id'), 1, 1);
        $user->update();
        return view('blueteam.home');
    }

    public function create(request $request){
        if($request->name == "") return view('blueteam.create'); 
        $team = new Team();
        $team->name = $request->name;
        $team->balance = 0;
        $team->blue = 1;
        $team->save();
        $user = Auth::user();
        $user->blueteam = substr(Team::all()->where('name', '=', $request->name)->pluck('id'), 1, 1);
        $user->update();
        return view('blueteam.home');
    }

    public function delete(request $request){
        $team = Team::all()->where('name', '=', $request->name);
        $team->delete();
        return view('home');
    }

}
