@component('mail::message')
# Приглашение в проект

Вы были приглашены в проект **{{$invitation->data['title']}}** в качестве **{{$type}}**

@isset($invitation->data['title'])
**Название**: {{$invitation->data['title']}}

@endisset
**Роль**: {{$invitation->role}}

@isset($invitation->data['dates'])
**Даты**: {{$invitation->data['dates']}}

@endisset
@isset($invitation->data['contact'])
**Контакт для связи**: {{$invitation->data['contact']}}

@endisset
@isset($invitation->data['description'])
**Описание**: {{$invitation->data['description']}}

@endisset
@if($invitation->data['attachLogline'] && isset($project->logline))
**Логлайн**: {{$project->logline}}

@endif
@if($invitation->data['attachSynopsis'] && isset($project->synopsis))
**Синопсис**: {{$project->synopsis}}

@endif

@component('mail::button', ['url' => $url['accept']])
    Подтвердить участие
@endcomponent
@component('mail::button', ['url' =>$url['reject']])
    Отказаться от участия
@endcomponent

С уважением,<br>
команда {{ config('app.name') }}
@endcomponent

