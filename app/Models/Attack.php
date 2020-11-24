<?php

namespace App\Models;

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
    protected $fillable = [ 'name', ];
    protected $casts = [ 'tags' => 'array', 'prereqs' => 'array', ]; // casts "json" database column to array and back

    public $_name    = "Abstract class - do not use";
    public $_tags    = [];
    public $_prereqs = [];
    public $_initial_difficulty = 3;
    public $_initial_detection_risk = 3;
    public $_initial_energy_cost = 100;
    public $possible = true;
    public $errormsg = "";

    function __construct() {
        $this->name           = $this->_name;
        $this->difficulty     = $this->_initial_difficulty;
        $this->detection_risk = $this->_initial_detection_risk;
        $this->tags           = $this->_tags;
        $this->prereqs        = $this->_prereqs;
        $this->success        = null;
        $this->detected       = null;
        $this->energy_cost    = $this->initial_energy_cost;
    }

    function onAttackComplete() { }

    public static function getAll(){
        $attackList = glob('Attacks/*');
        $attacks = [];
        foreach ($attackList as $attackName){
            $class = "\\App\\Models\\Attacks\\" . $attackName;
            $attacks[] = new $class;
        }
    }

    function onPreAttack() {
        $blueteam = Team::find($this->blueteam);
        $redteam  = Team::find($this->redteam);

        // Get all assets in both attacker's and target's inventories
        $item_ids = DB::table('inventory')->where('team_id', '=', $blueteam->id)->orWhere('team_id', '=', $redteam->id);
        $assets = Asset::all()->whereIn('id', $item_ids)->distinct();

        // Collect all tags and names of these assets to match against prerequisites for this attack
        $have = [];

        // Each asset has an opportunity to modify the attack object
        //
        foreach ($assets as $asset) {
            $asset->onPreAttack($this);
            $have[] = $asset->name;
            foreach ( $asset->tags as $tag ) { $have[] = $tag; }
        }
        $unmet_prereqs = set_diff($this->prereqs, $have);
        if ( count($unmet_prereqs) > 0 ) {
            $this->possible = false;
            $this->errormsg = "Unsatisfied prereqs for this attack";
        }
        if ( $blueteam->energy < $this->energy_cost ) {
            $this->possible = false;
            $this->errormsg = "Not enough energy available.";
        }
    }

}
