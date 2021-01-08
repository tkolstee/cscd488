<?php

namespace App\Models;

use App\Exceptions\TeamNotFoundException;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Exceptions\UserNotFoundException;

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

    public static function getByUsername($username){
        $user = User::all()->where('username','=',$username)->first();
        if($user == null){
            throw new UserNotFoundException();
        }
        return $user;
    }

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

    public function setEnergy($energy){
        $team = $this->getRedTeam();
        $team->setEnergy($energy);
    }

    public function getEnergy(){
        $team = $this->getRedTeam();
        $energy = $team->getEnergy();
        return $energy;
    }

    public function setTurnTaken($turn_taken){
        $team = $this->getBlueTeam();
        $team->setTurnTaken($turn_taken);
    }

    public function getTurnTaken(){
        $team = $this->getBlueTeam();
        $turn_taken = $team->getTurnTaken();
        if($turn_taken == null) $turn_taken = 0;
        return $turn_taken;
    }

    public function leaveBlueTeam() {
        $blueteam = $this->getBlueTeam();
        $this->blueteam = null;
        $this->update();
        if ($this->leader == 1){
            $members = $blueteam->members();
            if($members->isEmpty()){
                Team::destroy($blueteam->id);
            }else{
                $newLeader = $members->first();
                $newLeader->leader = 1;
                $newLeader->update();
            }
        }
    }

    public function changeLeader($newUsername){
        $blueteam = $this->getBlueTeam();
        $newUser = $this->getByUsername($newUsername);
        if($newUser->blueteam != $blueteam->id){
            return false;
        }
        $newUser->leader = 1;
        $newUser->update();
        $this->leader = 0;
        $this->update();
    }

    public function leaveRedTeam() {
        $redteam = $this->getRedTeam();
        $this->redteam = null;
        $this->update();
        Team::destroy($redteam->id);
    }

    public function joinBlueTeam($teamName) {
        $blueteam = Team::get($teamName);
        $this->blueteam = $blueteam->id;
        return $this->update();
    }

    public function createBlueTeam($teamName) {
        $team = Team::createBlueTeam($teamName);
        $this->blueteam = $team->id;
        $this->leader = 1;
        return $this->update();
    }

    public function createRedTeam($teamName) {
        $team = Team::createRedTeam($teamName);
        $this->redteam = $team->id;
        return $this->update();
    }
}
