<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\Log;
use App\Models\User;
use App\Services\AccessControlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        private readonly AccessControlService $accessControl,
    ) {}

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            $this->accessControl->record(
                'auth.failed',
                'Неудачная попытка авторизации: '.$request->string('email'),
                Log::FAIL,
                $request
            );

            return self::error('Неправильный email/пароль', 401);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->active) {
            Auth::logout();

            $this->accessControl->record(
                'auth.banned',
                'Попытка входа заблокированным пользователем: '.$user->email,
                Log::WARNING,
                $request,
                $user
            );

            return self::error('Учётная запись заблокирована', 403);
        }

        $this->accessControl->record(
            'auth.success',
            'Успешная авторизация в системе',
            Log::SUCCESS,
            $request,
            $user
        );

        $token = $user->createToken('API_TOKEN')->plainTextToken;

        return self::response($token, 'Успешная авторизация');
    }

    public function launchApp(Request $request)
    {
        $user = auth('sanctum')->user();

        $this->accessControl->record(
            $user ? 'auth.token.valid' : 'auth.token.invalid',
            $user
                ? 'Успешная авторизация в системе по токену'
                : 'Попытка входа по невалидному токену',
            $user ? Log::SUCCESS : Log::FAIL,
            $request,
            $user
        );

        return self::response(
            ['success' => (bool) $user],
            $user ? 'Токен валидный' : 'Токен не валиден',
            $user ? 200 : 401
        );
    }
}
