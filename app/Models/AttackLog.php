<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttackLog extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'attack_id',
        'blueteam_id',
        'redteam_id',
        'difficulty',
        'detection_chance'
    ];
}
