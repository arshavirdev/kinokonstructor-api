<tr>
    <td class="header">
        <a href="{{ config('front.base_url') }}" style="display: inline-block;">
            @if (trim($slot) === 'Laravel')
                <img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
            @else
                <img src="{{config('front.base_url')}}/logo.svg" class="logo" width="300">
                {{--                {{ $slot }}--}}
            @endif
        </a>
    </td>
</tr>
