<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use Auth;
use Redirect;

class HomeController extends Controller
{
    
    public function page($page, Request $request){
        switch($page){
            case("about"): return $this->about(); break;
            case("home"): return $this->index(); break;
            case("chooseteam"): return $this->chooseTeam(); break;
            default: return $this->index();
        }
    }

    public function chooseTeam(){
        if(!Auth::check()){
            return Redirect::route('login');
        }
        return view('home');
    }

    public function about(){
        return view('about');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('welcome');
    }
}
