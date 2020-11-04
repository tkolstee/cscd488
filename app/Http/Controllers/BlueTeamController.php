<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

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
}
