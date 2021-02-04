<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use View;
use Auth;

class UserController extends Controller {
    
    public function page($page, request $request){
        switch($page){
            case("settings"): return $this->settings($request); break;
            case("changename"): return $this->changeName($request); break;
            case("changeemail"): return $this->changeEmail($request); break;
            default: return view('welcome'); break;
        }
    }

    public function settings(request $request){
        $blueteam = Auth::user()->getBlueTeam();
        $redteam = Auth::user()->getRedTeam();
        return view('user/settings')->with(compact('blueteam','redteam'));
    }

    public function changeName(request $request){
        if(empty($request->name)){
            return view('user/changename');
        }
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
        ]);
        $user = Auth::user();
        $user->name = $request->name;
        $user->update();
        return $this->settings($request);
    }

    public function changeEmail(request $request){
        if(empty($request->email)){
            return view('user/changeemail');
        }
        $this->validate($request, [
            'email' => ['required', 'email', 'max:255'],
        ]);
        $user = Auth::user();
        $user->email = $request->email;
        $user->update();
        return $this->settings($request);
    }

}
