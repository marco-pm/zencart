<?php

namespace Seeders\InitialSeeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;

class TaxRatesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        Capsule::table('tax_rates')->truncate();

        Capsule::table('tax_rates')->insert(array(
            0 =>
                array(
                    'date_added' => '2023-06-29 12:14:04',
                    'last_modified' => '2023-06-29 12:14:04',
                    'tax_class_id' => 1,
                    'tax_description' => 'FL TAX 7.0%',
                    'tax_priority' => 1,
                    'tax_rate' => '7.0000',
                    'tax_rates_id' => 1,
                    'tax_zone_id' => 1,
                ),
        ));


    }
}
