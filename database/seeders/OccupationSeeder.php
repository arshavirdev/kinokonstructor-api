<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\Occupation;

class OccupationSeeder extends Seeder
{
    private static $actorOccupations = [
        "Актёр/Актриса",
    ];
    private static $occupations = [
        "Продюсер",
        "Режиссёр",
        "Сценарист",
        "Оператор",
        "Монтажёр",
        "Осветитель",
        "Звукорежиссёр",
        "Специалист по спецэффектам",
        "Каскадёр",
        "Костюмер",
        "Гримёр",
        "Кинокомпозитор",
    ];

    public function run()
    {
        if (DB::table('occupations')->count() > 0) {
            echo "\e[31m Occupations were not seeded as the table is not empty";
            return Occupation::all();
        }
        $actors = collect(self::$actorOccupations)->map(fn($occupation) => ['label' => $occupation, 'isActor' => true]);
        DB::table('occupations')->insert($actors->toArray());

        $specialists = collect(self::$occupations)->map(fn($occupation) => ['label' => $occupation,]);
        DB::table('occupations')->insert($specialists->toArray());

        return Occupation::all();
    }
}
