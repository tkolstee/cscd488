<?php

namespace Database\Factories;

use App\Models\AttackLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttackLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AttackLog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attack_id' => 1,
            'blueteam_id' => 1,
            'redteam_id' => 1,
            'difficulty' => 5,
            'detection_chance' => 1,
            'success' => false,
            'possible' => false,
        ];
    }
}
