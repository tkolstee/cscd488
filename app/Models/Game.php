<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Blueteam;
use App\Http\Controllers\SetupController;

class Game extends Model
{
    use HasFactory;

    public static function get() {
        $game = Game::all()->first();
        if($game == null){
            $controller = new SetupController();
            $controller->prefill_settings();
            $game = Game::all()->first();
        }
        return $game;
    }

    public static function turnNumber() {
        $game = Game::get();
        return $game->turn;
    }

    public static function endTurn() {
        $game = Game::get();
        $game->turn++;
        $game->save();
        $blueteams = Blueteam::all();
        foreach ($blueteams as $blueteam){
            $blueteam->turn_taken = 0;
            $blueteam->update();
        }
    }
}
