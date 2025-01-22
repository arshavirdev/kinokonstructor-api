@props([
    'project',
    'type',
])
@php
    $members = $project->memberInvites->where('type', $type)->map(fn($member) => [
        'role' => $member->role,
        'fullname' => $member->profile->fullname,
        'status' => $member->status
    ]);
    $custom_members = collect($project->custom_members)->where('type', $type)->map(fn($member) => [
        'role' => $member['role'],
        'fullname' => $member['fullname'],
        'status' => 'manual'
    ]);
    $statusOrder = ['accepted', 'manual', 'pending', 'rejected'];
    $members = $members
        ->concat($custom_members)
        ->sortBy(fn($member) => array_search($member['status'], $statusOrder) . $member['fullname']);
    $statuses = [
        'manual' => 'Добавлен вручную',
        'pending' => 'Запрос отправлен',
        'accepted' => 'Подтвержден',
        'rejected' => 'Отказался от участия',
    ]
@endphp
@if($members->count() > 0)
    <table width="100%">
    <thead>
        <tr>
        @if($type === 'partner')
            <th align="left">Тип партнерства</th> <th align="left">Название</th>
        @else
            <th align="left">Роль</th> <th align="left">ФИО</th> <th align="left">Статус</th>
        @endif
        </tr>
    </thead>
    @foreach ($members as $member)
        <tr>
            <td>{{$member['role']}}</td>
            <td>{{$member['fullname']}}</td>
            @if($type !== 'partner')<td>{{$statuses[$member['status']]}}</td>@endif
        </tr>
    @endforeach
    </table>
@else
    <span>Участники отсутствуют</span>
@endif
