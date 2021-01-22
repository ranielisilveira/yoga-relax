<?php

namespace App\Http\Controllers;

use App\Events\UserForgotPassword;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $user = User::where([
                'email' => $request->email
            ])->first();

            if (!$user) {
                throw new Exception(trans('messages.auth.invalid_data'));
            }

            if (!$user->is_verified) {
                throw new Exception(trans('messages.auth.unverified_user'));
            }

            $req = Request::create('/oauth/token', 'POST', [
                'grant_type' => 'password',
                'client_id' => env('PASSPORT_CLIENT_ID'),
                'client_secret' => env('PASSPORT_CLIENT_SECRET'),
                'username' => $request->email,
                'password' => $request->password,
            ]);

            $res = app()->handle($req);

            $responseBody = $res->getContent();
            $response = json_decode($responseBody, true);

            self::passportExceptions($request);

            return $response;
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function refresh(Request $request)
    {
        try {
            $req = Request::create('/oauth/token', 'POST', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $request->refresh_token,
                'client_id' => env('PASSPORT_CLIENT_ID'),
                'client_secret' => env('PASSPORT_CLIENT_SECRET'),
            ]);

            $res = app()->handle($req);

            $responseBody = $res->getContent();
            $response = json_decode($responseBody, true);

            self::passportExceptions($request);

            return $response;
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }


    public function logout(Request $request)
    {
        try {
            $request->user()->token()->revoke();

            auth()->user()->tokens->each(function ($token) {
                $token->delete();
            });

            return response([
                'message' => trans('messages.auth.logout_success')
            ]);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function forgotPassword(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        try {
            $user = User::where([
                'email' => $request->email
            ])->first();

            if (!$user) {
                throw new Exception(trans('messages.auth.invalid_data'));
            }

            if (!$user->is_verified) {
                throw new Exception(trans('messages.auth.unverified_user'));
            }

            $user->mail_token = Uuid::uuid4()->toString();
            $user->save();

            app()->setLocale($user->language);

            $url = env('APP_FRONT') . '/reset-password?mail_token=' . $user->mail_token;

            event(new UserForgotPassword($url, $user));

            return response([
                'message' => trans('messages.user_forgot_password.success')
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function resetPassword(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
            'mail_token' => 'required|min:36'
        ], [
            'mail_token.min' => trans('messages.user_reset_password.mail_token_invalid')
        ]);

        try {
            $user = User::where([
                'email' => $request->email,
                'mail_token' => $request->mail_token
            ])->first();

            if (!$user) {
                throw new Exception(trans('messages.auth.invalid_data'));
            }

            $user->mail_token = null;
            $user->password = Hash::make($request->password);
            $user->save();

            return response([
                'message' => trans('messages.user_reset_password.success')
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    private static function passportExceptions($response)
    {
        try {
            if (isset($response['error'])) {
                if ($response['error'] === 'invalid_client') {
                    throw new \Exception(trans('messages.auth.passport_error'));
                }

                if (
                    $response['error'] === 'invalid_credentials' ||
                    $response['error'] === 'invalid_request' ||
                    $response['error'] === 'invalid_grant' ||
                    $response['error'] === 'unsupported_grant_type'
                ) {
                    throw new \Exception(trans('messages.auth.invalid_data_try_again'));
                }


                throw new \Exception($response['error']);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
