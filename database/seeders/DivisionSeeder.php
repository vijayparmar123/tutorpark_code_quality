<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		\App\Models\Division::create(['name' => 'A','tag' => 'a']);
        \App\Models\Division::create(['name' => 'B','tag' => 'b']);
        \App\Models\Division::create(['name' => 'C','tag' => 'c']);
        \App\Models\Division::create(['name' => 'D','tag' => 'd']);
        \App\Models\Division::create(['name' => 'E','tag' => 'e']);
        \App\Models\Division::create(['name' => 'F','tag' => 'f']);
        \App\Models\Division::create(['name' => 'G','tag' => 'g']);
        \App\Models\Division::create(['name' => 'H','tag' => 'h']);
        \App\Models\Division::create(['name' => 'I','tag' => 'i']);
        \App\Models\Division::create(['name' => 'J','tag' => 'j']);
    }
}
