@component('mail::message')
# Подтверждение email

Уважаемый {{ $user->username }}, вы только что зарегистровались на [{{ config('app.name') }}]({{ config('front.base_url') }}).
Для завершения регистрации, Вам необходимо подтвердить Ваш адрес электронной почты

@component('mail::button', ['url' => $actionUrl])
    Подтвердить адрес
@endcomponent

@component('mail/components/button-fallback', ['url' => $actionUrl])
Уважаемый пользователь! В случае, если данная кнопка не работает, Вы можете подтвердить регистрацию, скопировав и вставив в строку браузера следующую ссылку:
@endcomponent

С уважением,<br>
команда {{ config('app.name') }}
@endcomponent
