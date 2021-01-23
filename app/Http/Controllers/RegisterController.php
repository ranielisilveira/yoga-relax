<?php

namespace App\Http\Controllers;

use App\Events\UserRegistered;
use App\Mail\UserConfirmationMail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $system_languages = explode(",", env("LANGUAGES"));
        $default_language = env("DEFAULT_LANG");
        $language = $request->language && in_array($request->language, $system_languages) ? $request->language : $default_language;
        app()->setLocale($language);

        $this->validate($request, [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'mail_token' => Uuid::uuid4()->toString(),
                'language' => $language
            ]);

            $url = env('APP_URL') . '/confirm?key=' . $user->mail_token;

            Mail::to($user->email)->send(
                new UserConfirmationMail($url, $user)
            );

            event(new UserRegistered($url, $user));

            return response([
                'message' => trans('messages.register.success')
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function confirm(Request $request)
    {
        try {
            $user = User::where([
                'mail_token' => $request->key,
                'is_verified' => false
            ])->first();

            $this->setLanguage($user);

            if (!$user) {
                throw new Exception(trans('messages.register.confirm_error'));
            }

            $user->mail_token = null;
            $user->is_verified = true;
            $user->save();

            $prefix = $user->language == 'pt' ? '' : $user->language;

            return redirect(env('APP_FRONT') . $prefix . '?confirm=success');
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    private function setLanguage($user)
    {
        try {
            app()->setLocale($user->language ?? env('DEFAULT_LANG'));
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
