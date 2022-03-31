<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RoleSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Maklad\Permission\Models\Role::create(['name' => 'admin']);
        \Maklad\Permission\Models\Role::create(['name' => 'tutor']);
        \Maklad\Permission\Models\Role::create(['name' => 'student']);
        \Maklad\Permission\Models\Role::create(['name' => 'parent']);
        \Maklad\Permission\Models\Role::create(['name' => 'school-admin']);
        \Maklad\Permission\Models\Role::create(['name' => 'school-tutor']);
        \Maklad\Permission\Models\Role::create(['name' => 'school-student']);
    }
}
