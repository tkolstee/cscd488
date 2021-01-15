<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Game;
use Auth;
use Illuminate\Support\Facades\Validator;


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
        Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        return view('admin.userSignUp');
    }
}

