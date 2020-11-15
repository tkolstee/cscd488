<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    public static function get() {
        return Game::all()->first();
    }

    public static function turnNumber() {
        $game = Game::get();
        return $game->turn;
    }

    public static function endTurn() {
        $game = Game::get();
        $game->turn++;
        $game->save();
    }
}
