<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountsToPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->integer("used_items_count")->after("has_won");
            $table->integer("used_item_combos_count")->after("used_items_count");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn("used_items_count");
            $table->dropColumn("used_item_combos_count");
        });
    }
}
