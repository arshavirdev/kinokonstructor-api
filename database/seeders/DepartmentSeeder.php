<?php

namespace Database\Seeders;

use App\Models\Department;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (DB::table('departments')->count() > 0) {
            echo "\e[31m departments were not seeded as the table is not empty";
            return;
        }

        $departments = [
            'Сценарная группа',
            'Режиссерская группа',
            'Операторская группа',
            'Актеры',
            'Художники и Декораторы',
            'Звукооператоры и Звукорежиссеры',
            'Группа Монтажа',
            'Компьютерная Графика и Спецэффекты',
            'Трюковые Съемки',
            'Киномузыка',
            'Бухгалтеры и Юристы',
            'Административная группа',
            'Продюсерская группа',
            'Менеджеры по Локациям',
            'Водители и Рабочие',
        ];

        $insertData = collect($departments)
            ->map(fn($department) => ['label' => $department])
            ->toArray();
        DB::table('departments')->insert($insertData);
    }
}
