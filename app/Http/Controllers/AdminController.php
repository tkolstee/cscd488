<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Game;


class AdminController extends Controller {

    public function page(Request $request, $page) {
        $this->process_form_data($request);
        switch ($page) {
            default:
                return view('admin.home');
                break;
        }
        abort(404);
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

