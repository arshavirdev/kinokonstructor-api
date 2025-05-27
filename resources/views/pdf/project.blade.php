@php
    $genres = [
        "Биографический",
        "Боевик",
        "Вестерн",
        "Военные",
        "Детектив",
        "Детские",
        "Драма",
        "Исторический",
        "Комедия",
        "Короткометражный",
        "Криминал",
        "Мелодрама",
        "Мистика",
        "Музыкальный",
        "Мультипликационный фильм",
        "Научно-популярный",
        "Нуар",
        "Приключения",
        "Семейный",
        "Спортивный",
        "Триллер",
        "Ужасы",
        "Фантастика",
        "Фэнтези",
    ];
    $statuses = [
        'draft' => 'Черновик',
        'moderation' => 'На модерации',
        'accepted' => 'Одобрен',
        'rejected' => 'Требует правок',
    ];
    $projectGenres = array_map(fn($id) => $genres[$id - 1], $project->genres)
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Проект #{{ $project->id }}</title>
        <style>
            body {
                /*font-family: Helvetica, "Times New Roman", Courier, inherit;*/
                font-size: 12px;
            }
            h1{ margin-bottom: 0;}
            h3{ margin-bottom: 0; }
            section {margin-top: 0.5rem;}
            p{ margin-top: 0; }
            .text-xs{ font-size: 6px; }
            .text-sm{ font-size: 8px; }
            .text-md{ font-size: 10px; }
            .font-mono{ font-family: monospace; }
            .table-desc td:nth-child(1){ font-weight: bold; }
            .table-desc td:nth-child(2){ padding-left: 1rem }
            .ref{font-family: monospace; font-size: 10px;}
            .ml-3{margin-left: 3rem;}
        </style>
    </head>
    <body>
    <htmlpageheader name="_default">
        <table width="100%" style="font-family: monospace; font-size: 10px;">
            <tr style="vertical-align: top">
                <td>Проект #{{$project->id}} <br></td>
                <td width="auto" class="barcodecell" style="height: 16px" align="center">
                    <barcode code="000346987353469{{87355 + $project->id}}" type="EAN128C" size="0.8" />
                    <div style="font-family: ocrb;">(00) 0346987 353469{{87355 + $project->id}}</div>
                </td>
            </tr>
        </table>
    </htmlpageheader>
    <sethtmlpageheader name="_default" value="on" show-this-page="1" />
    <htmlpagefooter name="_default">
        <table width="100%" style="font-family: monospace; font-size: 10px;">
            <tr>
                <td width="50%">Страница сгенерирована {{ date('Y-m-d H:i:s') }}</td>
                <td width="50%" style="text-align: right; ">{PAGENO}/{nbpg}</td>
            </tr>
        </table>
    </htmlpagefooter>
    <sethtmlpagefooter name="_default" value="on" show-this-page="1" />

    <h1>{{ $project->title }}</h1>
    <section>
    <table><tr><td>
        <table class="table-desc">
            <tr><td>Тип</td><td>{{$project->genre_type === 'documentary' ? 'Документальный' : 'Художественный'}} {{$project->format === 'movie' ? 'Фильм' : 'Сериал'}}</td></tr>
            <tr><td>Хронометраж</td><td>{{$project->chronography}} мин.</td></tr>
            <tr><td>Кол-во серий</td><td>{{$project->series_count}}</td></tr>
            <tr><td>Жанры</td><td>{{implode(', ',$projectGenres)}}</td></tr>
        </table>
    </td><td width="25px"></td><td>
        <table class="table-desc">
            <tr><td>Статус</td><td>{{$statuses[$project->status]}}</td></tr>
            <tr><td>Создан</td><td>{{$project->owner->fullname}} [id: {{$project->owner->id}}]</td></tr>
            <tr><td>Дата создания</td><td>{{$project->created_at}}</td></tr>
            <tr><td>Дата изменения</td><td>{{$project->updated_at}}</td></tr>
        </table>
    </td></tr></table>
    </section>
    <section>
        <h2>1. Описание</h2>
        <h3>Логлайн</h3>
        <p>{{$project->logline}}</p>
        <h3>Синопсис</h3>
        <p>
            {{$project->synopsis}}<br>
            <x-project-attachment :type="\App\Models\Project::EXTENDED_SYNOPSIS_MEDIA" :files="$files"/>
        </p>

        <h3>Актуальность проекта</h3>
        <p>{{$project->relevance}}</p>
        <h3>Дополнительная информация</h3>
        <p>
            {{$project->additional}}<br>
            <x-project-attachment :type="\App\Models\Project::ATTACHMENTS_MEDIA" :files="$files"/>
        </p>

    </section>
    <section>
        <h2>2. Авторы проекта</h2>
        <x-project-member :project="$project" type="author"/>
    </section>
    <section>
        <h2>3. Команда проекта</h2>
        <x-project-member :project="$project" type="team"/>
    </section>
    <section>
        <h2>4. Актерский состав</h2>
        <x-project-member :project="$project" type="cast"/>
        <x-project-attachment :type="\App\Models\Project::CAST_MEDIA" :files="$files"/>
    </section>
    <section>
        <h2>5. Визуал</h2>
        <h3>Костюмы</h3>
        <x-project-attachment :type="\App\Models\Project::COSTUMES_MEDIA" :files="$files"/>
        <h3>Грим</h3>
        <x-project-attachment :type="\App\Models\Project::MAKEUP_MEDIA" :files="$files"/>
        <h3>Декорации</h3>
        <x-project-attachment :type="\App\Models\Project::DECORATIONS_MEDIA" :files="$files"/>
        <h3>Аудиореференсы</h3>
        @if($project->audio_reference)
            <a href="{{$project->audio_reference}}">{{$project->audio_reference}}</a>
        @else
            <p>Аудиореференсы не добавлены</p>
        @endif
        <h3>Места съемок</h3>
        <x-project-attachment :type="\App\Models\Project::LOCATIONS_MEDIA" :files="$files"/>
        <h3>Локации</h3>
        @if($project->locations->count() > 0)
        <ul>
        @foreach($project->locations as $location)
            <li><span>[id: {{$location->id}}, <a href="{{ config('front.base_url') }}/locations/{{$location->id}}">{{ config('front.base_url') }}/locations/{{$location->id}}</a> ]:</span> {{$location->name}}</li>
        @endforeach
        </ul>
        @else
        <p>Локации не добавлены</p>
        @endif
    </section>
    <section>
        <h2>6. Бюджет</h2>
        <table class="table-desc">
            @php
                $number_formatter = new NumberFormatter("ru-RU", NumberFormatter::DECIMAL);
            @endphp
            <tr><td>Предполагаемый бюджет</td><td align="right">{{$number_formatter->format($project->budget)}} руб.</td></tr>
            <tr><td>Софинансирование проекта</td><td align="right">{{$number_formatter->format($project->co_financing)}} руб.</td></tr>
        </table>
        <p>
        <x-project-attachment :type="\App\Models\Project::FINANCIAL_PLAN_MEDIA" :files="$files"/>
        <x-project-attachment :type="\App\Models\Project::FINANCIAL_PROOF_MEDIA" :files="$files"/>
        </p>
    </section>
    <section>
        <h2>7. Партнеры проекта</h2>
{{--        <pre>{{var_dump($project->toArray())}}</pre>--}}
        <x-project-member :project="$project" type="partner"/>
        <x-project-attachment :type="\App\Models\Project::PARTNERSHIP_PROOF_MEDIA" :files="$files"/>
    </section>
    </body>
</html>
