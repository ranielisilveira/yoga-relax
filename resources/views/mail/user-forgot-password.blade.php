@component('mail::message')
# {{ trans('mail.user_forgot_password.hello') }}, {{ $user->email }}

{{ trans('mail.user_forgot_password.markdown_line_1') }}


@component('mail::button', ['url' => $url])
{{ trans('mail.user_forgot_password.button_text') }}
@endcomponent

{{ trans('mail.user_forgot_password.confirm_email_text') }}
 [{{ $url  }}]({{ $url }}),

{{ trans('mail.user_forgot_password.markdown_line_2') }}<br>
{{ env('APP_NAME') }}
@endcomponent
