<?php

namespace Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePluginControlVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create('plugin_control_versions', function (Blueprint $table) {
            $table->string('unique_key', 40);
            $table->string('version', 10);
            $table->string('author', 64);
            $table->text('zc_versions');
            $table->boolean('infs')->default(0);

            $table->primary(['unique_key', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists('plugin_control_versions');
    }
}
