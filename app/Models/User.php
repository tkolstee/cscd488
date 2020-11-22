<?php

namespace App\Models;

use App\Exceptions\TeamNotFoundException;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'blueteam',
        'leader',
        'redteam',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getBlueTeam() {
        $blueteam =  Team::find($this->blueteam);
        if ($blueteam == null){ throw new TeamNotFoundException();}
        return $blueteam;
    }

    public function getRedTeam() {
        $redteam = Team::find($this->redteam);
        if ($redteam == null){ throw new TeamNotFoundException();}
        return $redteam;
    }

    public function leaveBlueTeam() {
        $blueteam = $this->getBlueTeam();

        $this->blueteam = null;
        if ($this->leader == 1){
            $members = $blueteam->members();
            if($members->isEmpty()){
                Team::destroy($this->blueteam);
            }else{
                $newLeader = $members->first();
                $newLeader->leader = 1;
                $newLeader->update();
            }
        }
        return $this->update();
    }

    public function leaveRedTeam() {
        $redteam = $this->getRedTeam();
        $this->redteam = null;
        $this->update();
        Team::destroy($redteam->id);
    }

    public function joinBlueTeam($teamName) {
        $blueteam = Team::all()->where('name', '=', $teamName)->first();
        if($blueteam == null) throw new TeamNotFoundException();
        $this->blueteam = $blueteam->id;
        return $this->update();
    }

    public function createBlueTeam($team) {
        $this->blueteam = $team->id;
        $this->leader = 1;
        return $this->update();
    }

    public function createRedTeam($team) {
        $this->redteam = $team->id;
        return $this->update();
    }

    public function deleteTeam($team) {
        if ($team->blue == 1 && $this->leader == 1){
            $this->leader == 0;
            $this->update();
        }
        return Team::destroy($team->id);
    }
}
