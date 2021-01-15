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
        if (Auth::user()->isAdmin()) {
            switch ($page) {
                default:
                    return view('admin.home');
                    break;
                case 'userSignUp': return $this->userSignUp($request); break;
            }
        }
        else {
            abort(404);
        }
    }

    public function process_form_data(Request $request) {
        $action = $request->action;
        switch($action) {
            case 'next-turn':
                Game::endTurn();
                break;
            default:
                break;
        }
    }

    public function userSignUp(request $request) {
        if (empty($request->name)) {
            return view('admin.userSignUp');
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
        return view('admin.userSignUp')->with(compact('message'));
    }
}

