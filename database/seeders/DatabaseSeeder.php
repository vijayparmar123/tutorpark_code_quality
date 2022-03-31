<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call('UsersTableSeeder');
        $this->call(RoleSeed::class);
        $this->call(DivisionSeeder::class);
        $this->call(ModuleSeeder::class);
        $this->call(PermissionSeed::class);
        $this->call(QuestionTypesSeeder::class);
        $this->call(SectionSeeder::class);
    }
}
