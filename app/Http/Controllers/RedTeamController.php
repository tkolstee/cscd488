<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

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
}
