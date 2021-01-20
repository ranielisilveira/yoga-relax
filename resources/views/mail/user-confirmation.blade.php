@component('mail::message')
# {{ trans('mail.user_confirmation.hello') }}, {{ $user->email }}

{{ trans('mail.user_confirmation.welcome') }}

{{ trans('mail.user_confirmation.markdown_line_1') }}



@component('mail::button', ['url' => $url])
{{ trans('mail.user_confirmation.button_text') }}
@endcomponent

{{ trans('mail.user_confirmation.confirm_email_text') }}
 [{{ $url  }}]({{ $url }}),

{{ trans('mail.user_confirmation.markdown_line_2') }}<br>
{{ env('APP_NAME') }}
@endcomponent
