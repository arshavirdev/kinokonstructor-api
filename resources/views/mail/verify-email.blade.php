@component('mail::message')
# Подтверждение email

Уважаемый {{ $user->username }}, вы только что зарегистровались на [{{ config('app.name') }}]({{ env('SPA_URL') }}).
Для завершения регистрации, Вам необходимо подтвердить Ваш адрес электронной почты

@component('mail::button', ['url' => $actionUrl])
    Подтвердить адрес
@endcomponent

С уважением,<br>
команда {{ config('app.name') }}
@endcomponent
