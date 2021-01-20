<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                throw new Exception('Dados inválidos.');
            }

            if (!$user->is_verified) {
                throw new Exception('Email ainda não verificado, você deve confirmar sua conta para logar.');
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

            if (isset($response['error'])) {
                if ($response['error'] === 'invalid_client') {
                    throw new \Exception('Problemas no servidor de autenticação (passport). Tente Novamente mais tarde.');
                }

                if ($response['error'] === 'invalid_credentials') {
                    throw new \Exception('Dados Inválidos. Tente Novamente.');
                }

                if ($response['error'] === 'invalid_request') {
                    throw new \Exception('Dados Inválidos. Tente Novamente.');
                }

                if ($response['error'] === 'invalid_grant') {
                    throw new \Exception('Dados Inválidos. Tente Novamente.');
                }

                if ($response['error'] === 'unsupported_grant_type') {
                    throw new \Exception('Dados Inválidos. Tente Novamente.');
                }

                throw new \Exception($response['error']);
            }

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

            if (isset($response['error'])) {
                if ($response['error'] === 'invalid_client') {
                    throw new \Exception('Problemas no servidor de autenticação (passport). Tente Novamente mais tarde.');
                }

                if ($response['error'] === 'invalid_credentials') {
                    throw new \Exception('Dados Inválidos. Tente Novamente.');
                }

                if ($response['error'] === 'invalid_request') {
                    throw new \Exception('Dados Inválidos. Tente Novamente.');
                }

                if ($response['error'] === 'invalid_grant') {
                    throw new \Exception('Dados Inválidos. Tente Novamente.');
                }

                if ($response['error'] === 'unsupported_grant_type') {
                    throw new \Exception('Dados Inválidos. Tente Novamente.');
                }

                throw new \Exception($response['error']);
            }

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
                'message' => 'Você deslogou com sucesso.'
            ]);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
