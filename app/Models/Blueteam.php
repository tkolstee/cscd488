<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\TeamNotFoundException;

class Blueteam extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'team_id',
        'turn_taken',
    ];

    public static function get($id){
        $blueteam = Blueteam::all()->where('team_id','=',$id)->first();
        if($blueteam == null) $blueteam = Blueteam::create($id);
        return $blueteam;
    }

    public static function create($team_id){
        $blueteam = new Blueteam();
        $blueteam->team_id = $team_id;
        $blueteam->turn_taken = 0;
        $blueteam->save();
        return $blueteam;
    }

    public function setTurnTaken($turn_taken){
        $this->turn_taken = $turn_taken;
        return $this->update();
    }
}
