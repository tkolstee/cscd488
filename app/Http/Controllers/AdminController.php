<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Game;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\Hash;


class AdminController extends Controller {

    public function page(Request $request, $page) {
        $this->process_form_data($request);
        switch ($page) {
            default:
                return view('admin.home');
                break;
            case 'playerRegistration': return $this->playerRegistration($request); break;
        }
    }

    public function process_form_data(Request $request) {
        $action = $request->action;
        switch($action) {
            case 'next-turn':
                Game::endTurn();
                break;
            case 'toggle-prereqs':
                Game::toggleDisablePrereqs();
                break;
            default:
                break;
        }
    }

    public function playerRegistration(request $request) {
        if (empty($request->name)) {
            return view('admin.playerRegistration');
        }
        
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request['password']),
            'is_admin' => 0
        ]);
        $message = "User created successfully!";
        return view('admin.playerRegistration')->with(compact('message'));
    }
}

