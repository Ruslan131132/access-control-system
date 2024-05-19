<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('login');
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'required' => 'Это поле обязательно',
        ]);

        if (! Auth::attempt($credentials)) {
            Log::create([
                'type' => Log::FAIL,
                'text' => 'Неудачная попытка авторизации в CRM: '.$credentials['email'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'source' => 'app',
            ]);

            return redirect()->back()->withErrors(['auth' => 'Неправильный логин/пароль']);
        }

        /** @var User $user */
        $user = Auth::user();
        if (! $user->active || ! $user->hasRole('admin')) {
            Auth::logout();

            return redirect()->back()->withErrors(['auth' => 'В доступе отказано']);
        }

        return redirect()->route('main')->with(['success' => 'Добро пожаловать, '.$user->name]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
