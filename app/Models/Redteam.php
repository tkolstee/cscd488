<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\TeamNotFoundException;

class Redteam extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'team_id',
        'energy',
    ];

    public static function get($id){
        $redteam = Redteam::all()->where('team_id','=',$id)->first();
        if($redteam == null) $redteam = Redteam::create($id);
        return $redteam;
    }

    public static function getEnergy($teamID){
        $redteam = Redteam::get($teamID);
        return $redteam->energy;
    }

    public static function create($team_id){
        $redteam = new Redteam();
        $redteam->team_id = $team_id;
        $redteam->energy = 1000;
        $redteam->save();
        return $redteam;
    }

    public function setEnergy($energy){
        $this->energy = $energy;
        return $this->update();
    }

    public function useEnergy($energyCost){
        $this->energy -= $energyCost;
        return $this->update();
    }
    
}
