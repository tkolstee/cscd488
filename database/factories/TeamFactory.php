<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Team::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'balance' => 0,
            'reputation' => 0,
            'blue' => 1,
        ];
    }

    /**
     * Indicate that the team is red
     * 
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function red(){
        return $this->state(function (array $attributes){
            return [
                'blue' => 0,
            ];
        });
    }
}
