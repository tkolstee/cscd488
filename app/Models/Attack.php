<?php

namespace App\Models;

use App\Exceptions\TeamNotFoundException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attack extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'possible',
        'difficulty',
        'detection_chance'
    ];

    public function onAttackComplete($attackLog){
        $redteam = Team::find($attackLog->redteam_id);
        $blueteam = Team::find($attackLog->blueteam_id);
        if($redteam == null || $blueteam == null){
            throw new TeamNotFoundException();
        }
        
        if ($attackLog->success) {
            //All sample stuff for what it could look like.
            $redteam->balance += 1000;
            $redteam->update();
            $blueteam->balance -= 500;
            if ($blueteam->balance < 0){
                $blueteam->balance = 0;
            }
            $blueteam->update();
        }
    }
}
