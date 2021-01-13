<?php

namespace Database\Factories;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Inventory::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
   public function definition()
   {
       return [
           'quantity' => 1,
           'team_id' => 1,
           'asset_name' => "Firewall",
           'level' => 1,
           'info' => null,
       ];
   }
   /**
     * Indicate that the quantity is many
     * 
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function many(){
        return $this->state(function (){
            return [
                'quantity' => 5,
            ];
        });
    }
}
