<?php

namespace Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGetTermsToFilterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create('get_terms_to_filter', function (Blueprint $table) {
            $table->string('get_term_name', 191)->default('')->primary();
            $table->string('get_term_table', 64);
            $table->string('get_term_name_field', 64);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists('get_terms_to_filter');
    }
}
