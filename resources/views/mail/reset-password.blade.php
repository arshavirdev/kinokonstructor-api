@component('mail::message')
# Сброс пароля

Уважаемый {{ $user->username }}, мы только что получили запрос на сброс пароля для вашего аккаунта на [{{ config('app.name') }}]({{ config('front.base_url') }}).
Для сброса пароля перейдите по ссылке, нажав на кнопку ниже. Ссылка действительна в течении {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} минут.

@component('mail::button', ['url' => $actionUrl])
    Сбросить пароль
@endcomponent

Если Вы не запрашивали сброс пароля, просто игнорируйте данное письмо.

С уважением,<br>
команда {{ config('app.name') }}
@endcomponent

