<?php

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Asset::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'type' => 1,
            'blue' => 1,
            'buyable' => 1,
            'purchase_cost' => 100,
            'ownership_cost' => 1,
        ];
    }

    /**
     * Indicate that the asset is red
     * 
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function red(){
        return $this->state(function (array $attributes){
            return [
                'blue' => 0,
                'purchase_cost' => 200,
                'ownership_cost' => 2,
            ];
        });
    }
}
