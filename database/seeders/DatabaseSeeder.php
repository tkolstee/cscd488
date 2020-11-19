<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        \App\Models\Asset::factory()->create([
            'name' => 'SQLDatabase',
            'type' => 1,
            'purchase_cost' => 100,
            'ownership_cost' => 1,
            'buyable' =>1,
        ]);
        \App\Models\Asset::factory()->red()->create([
            'name' => 'testAssetRed',
            'type' => 1,
            'purchase_cost' => 200,
            'ownership_cost' => 2,
            'buyable' => 1,
        ]);

        $attack = new \App\Models\Attack();
        $attack->name = "SQLInjection";
        $attack->difficulty = 5;
        $attack->detection_chance = 1;
        $attack->save();

        $game = new \App\Models\Game();
        $game->save();

        \App\Models\Setting::set('turn_end_time', '7:00');
    }
}
