<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Game;
use Auth;


class AdminController extends Controller {

    public function page(Request $request, $page) {
        $this->process_form_data($request);
        if (Auth::user()->isAdmin()) {
            switch ($page) {
                default:
                    return view('admin.home');
                    break;
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


}

