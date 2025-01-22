@props([
    'files',
    'type',
])
@if($files[$type] !== false)
@foreach ($files[$type] as $file)
<span class="ref"><dottab/>[ Прикреплен файл: {{$file['name']}} ]</span><br>
@endforeach
@else
<span class="ref"><dottab/> [ Прикрепления отсутствуют ]</span>
@endif
