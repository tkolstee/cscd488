<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Blueteam;
use App\Models\Team;
use App\Models\Bonus;
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
            $blueteam->setTurnTaken(0);
            $team = Team::find($blueteam->team_id);
            $team->useTurnConsumables();
            $team->addBonusReputation();
            $revGained = $team->getPerTurnRevenue();
            $revGainedActual = $revGained;
            //Add bonus stuff here
            $bonuses = $team->getBonusesByTarget();
            foreach($bonuses as $bonus){
                if(in_array("RevenueDeduction", $bonus->tags)){
                    $revGainedActual -= $revGained * $bonus->percentRevDeducted;
                }
                if(in_array("ReputationDeduction", $bonus->tags)){
                    //Reputation stuff
                }
            }
            $team->balance += $revGained;
        }
        $bonuses = Bonus::all();
        foreach($bonuses as $bonus){
            $bonus->onTurnChange();
        }
        $redteams = Redteam::all();
        foreach($redteams as $redteam){
            $redteam->setEnergy(1000);
        }
    }
}
