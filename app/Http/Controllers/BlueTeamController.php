<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Models\Team;

class BlueTeamController extends Controller {

    public function page($page, Request $request) {

        switch ($page) {
            case 'home': return view('blueteam.home'); break;
            case 'create': return view('blueteam.create'); break;
            case 'join': return index(); break;
            case 'planning': return view('blueteam.planning'); break;
            case 'status': return view('blueteam.status'); break;
            case 'store': return view('blueteam.store'); break;
            case 'training': return view('blueteam.training'); break;
        }

    }

    public function index(){
        $blueteams = Teams::where('blue', '=', 1);
        return View::make('blueteam.join')->with('blueteams', $blueteams);
    }

}
