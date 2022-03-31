<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use \App\Models\Module;

class PermissionSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('maklad.permission.cache');
        
        $modules = Module::orderBy('created_at', 'desc')->take(5)->get();
        // $modules = Module::orderBy('created_at', 'desc')->get();
        foreach ($modules as $module) {
            \Maklad\Permission\Models\Permission::create(['name' => $module->tag . '_view']);
            \Maklad\Permission\Models\Permission::create(['name' => $module->tag . '_add']);
            \Maklad\Permission\Models\Permission::create(['name' => $module->tag . '_edit']);
            \Maklad\Permission\Models\Permission::create(['name' => $module->tag . '_delete']);
        }

        
    }
}
