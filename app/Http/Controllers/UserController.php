<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use View;
use Auth;
use App\Rules\MatchOldPassword;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller {
    
    public function page($page, request $request){
        switch($page){
            case("settings"): return $this->settings($request); break;
            case("changename"): return $this->changeName($request); break;
            case("changeemail"): return $this->changeEmail($request); break;
            case("changeusername"): return $this->changeUserName($request); break;
            case("changepassword"): return $this->changePassword($request); break;
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

    public function changeUserName(request $request){
        if(empty($request->username)){
            return view('user/changeusername');
        }
        $this->validate($request, [
            'username' => ['required', 'unique:users', 'string', 'max:255'],
        ]);
        $user = Auth::user();
        $user->username = $request->username;
        $user->update();
        return $this->settings($request);
    }

    public function changePassword(request $request){
        if(empty($request->oldPassword) || empty($request->newPassword) || strlen($request->newPassword) < 8){
            return view('user/changepassword');
        }
        $request->validate([
            'oldPassword' => ['required', new MatchOldPassword],
            'newPassword' => ['required'],
            'newPasswordConfirm' => ['same:newPassword'],
        ]);
        $user = Auth::user();
        $user->password = Hash::make($request->newPassword);
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
