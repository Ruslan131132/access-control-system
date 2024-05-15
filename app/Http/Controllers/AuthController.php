<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            Log::create([
                'type' => Log::FAIL,
                'text' => 'Неудачная попытка авторизации: '.$request->string('email'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'source' => 'app',
            ]);

            return self::error('Неправильный email/пароль', 401);
        }

        /** @var User $user */
        $user = Auth::user();
        if (! $user->active) {
            Auth::logout();

            return self::error('Учётная запись заблокирована', 403);
        }

        Log::create([
            'type' => Log::SUCCESS,
            'text' => 'Успешная авторизация в системе',
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'source' => 'app',
        ]);

        return self::response($user->createToken('API_TOKEN')->plainTextToken, 'Успешная авторизация');
    }

    public function launchApp(Request $request)
    {
        $user = auth('sanctum')->user();

        return self::response(
            ['success' => (bool) $user],
            $user ? 'Токен валидный' : 'Токен не валиден',
            $user ? 200 : 401
        );
    }
}
