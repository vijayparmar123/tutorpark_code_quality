<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RenameTimeColumnsToSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // DB::statement("db.sessions.updateMany({},{$rename : {"start_time" : "start_date"},{"end_time" : "end_date"}})");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // DB::statement("db.sessions.updateMany({},{$rename : {"start_date" : "start_time"},{"end_date" : "end_time"}})");
    }
}
