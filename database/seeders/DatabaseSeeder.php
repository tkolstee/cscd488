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
        $game = new \App\Models\Game();
        $game->save();

        \App\Models\Setting::set('turn_end_time', '7:00');
    }
}
