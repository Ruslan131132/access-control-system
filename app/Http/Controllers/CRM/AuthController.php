<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
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
            $this->accessControl->record(
                'crm.auth.failed',
                'Неудачная попытка авторизации в CRM: '.$credentials['email'],
                Log::FAIL,
                $request
            );

            return redirect()->back()->withErrors(['auth' => 'Неправильный логин/пароль']);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->active || ! $user->hasRole('admin')) {
            Auth::logout();

            $this->accessControl->record(
                'crm.auth.denied',
                'Отказ в доступе к CRM для пользователя: '.$credentials['email'],
                Log::WARNING,
                $request,
                $user
            );

            return redirect()->back()->withErrors(['auth' => 'В доступе отказано']);
        }

        $this->accessControl->record(
            'crm.auth.success',
            'Успешный вход в CRM: '.$user->email,
            Log::SUCCESS,
            $request,
            $user
        );

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
