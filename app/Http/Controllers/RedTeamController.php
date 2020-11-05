<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Team;
Use Auth;
Use View;

class RedTeamController extends Controller {

    public function page($page, Request $request) {
        switch ($page) {
            case 'home': return view('redteam.home'); break;
            case 'attacks': return view('redteam.attacks'); break;
            case 'learn': return view('redteam.learn'); break;
            case 'store': return view('redteam.store'); break;
            case 'status': return view('redteam.status'); break;
        }
    }

    public function create(request $request){
        if($request->name == "") return view('redteam.create'); 
        $request->validate([
            'name' => ['required', 'unique:teams', 'string', 'max:255'],
        ]);
        $team = new Team();
        $team->name = $request->name;
        $team->balance = 0;
        $team->blue = 0;
        $team->save();
        $user = Auth::user();
        $user->redteam = substr(Team::all()->where('name', '=', $request->name)->pluck('id'), 1, 1);
        $user->update();
        return view('redteam.home');
    }

    public function delete(request $request){
        $team = Team::all()->where('name', '=', $request->name);
        $team->delete();
        return view('home');
    }
}
