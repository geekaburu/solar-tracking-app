<?php

use Illuminate\Database\Seeder;

class PanelControlsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\PanelControl::create([
            'user_id' => 2,
            'angle' => 90,
        ]);
    }
}
