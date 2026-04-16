<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\City;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('city')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        City::insert([
            ['country_id' => 1, 'pavadinimas' => 'Vilnius'],
            ['country_id' => 1, 'pavadinimas' => 'Kaunas'],
            ['country_id' => 1, 'pavadinimas' => 'Klaipėda'],
            ['country_id' => 1, 'pavadinimas' => 'Šiauliai'],
            ['country_id' => 1, 'pavadinimas' => 'Panevėžys'],

            ['country_id' => 2, 'pavadinimas' => 'Rīga'],
            ['country_id' => 2, 'pavadinimas' => 'Daugavpils'],
            ['country_id' => 2, 'pavadinimas' => 'Jelgava'],

            ['country_id' => 3, 'pavadinimas' => 'Tallinn'],
            ['country_id' => 3, 'pavadinimas' => 'Tartu'],
            ['country_id' => 3, 'pavadinimas' => 'Narva'],
        ]);
    }
}
