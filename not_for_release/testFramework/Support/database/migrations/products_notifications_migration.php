<?php

namespace Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductsNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create('products_notifications', function (Blueprint $table) {
            $table->integer('products_id')->default(0);
            $table->integer('customers_id')->default(0);
            $table->dateTime('date_added')->default('0001-01-01 00:00:00');

            $table->primary(['products_id', 'customers_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists('products_notifications');
    }
}
