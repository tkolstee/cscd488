<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

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

        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@email.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'is_admin' => 1,
        ]);

        for ($i = 0; $i < 6; $i++){
            User::create([
                'name' => $i,                   //1
                'email' => $i . '@email.com',   //1@email.com
                'username' => 'user'.$i,        //user1
                'password' => bcrypt('password'), //password
                'is_admin' => 0,
            ]);
        }
    }
}
