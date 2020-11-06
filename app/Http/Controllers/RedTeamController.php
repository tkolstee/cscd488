<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Team;
Use Auth;
Use View;

class RedTeamController extends Controller {

    public function page($page, Request $request) {
        $redteam = Team::find(Auth::user()->redteam);
        switch ($page) {
            case 'home': return view('redteam.home')->with('redteam',$redteam); break;
            case 'attacks': return view('redteam.attacks')->with('redteam',$redteam); break;
            case 'learn': return view('redteam.learn')->with('redteam',$redteam); break;
            case 'store': return view('redteam.store')->with('redteam',$redteam); break;
            case 'status': return view('redteam.status')->with('redteam',$redteam); break;
            case 'create': return create($request); break;
        }
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
        $team->delete();
        return view('home');
    }
}
