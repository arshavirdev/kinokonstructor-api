<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\Occupation;

class RegionSeeder extends Seeder
{
    private static $regions = [
        "Москва и МО",
        "Ленинградская область",
        "Алтайский край",
        "Амурская область",
        "Архангельская область",
        "Астраханская область",
        "Белгородская область",
        "Брянская область",
        "Владимирская область",
        "Волгоградская область",
        "Вологодская область",
        "Воронежская область",
    ];

    public function run()
    {
        if (DB::table('regions')->count() > 0) {
            echo "\e[31m Regions were not seeded as the table is not empty";
            return Region::all();
        }

        $regions = collect(self::$regions)->map(fn($region) => ['label' => $region]);
        DB::table('regions')->insert($regions->toArray());

        return Region::all();
    }
}
