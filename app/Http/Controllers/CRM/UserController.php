<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\QrUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->with(['todayLogs', 'roles'])
            ->addSelect([
                'last_activation_at' => QrUser::query()
                    ->select('updated_at')
                    ->whereColumn('user_id', 'users.id')
                    ->where('active', false)
                    ->latest('updated_at')
                    ->limit(1),
            ])
            ->when($request->filled('email'), function ($query) use ($request) {
                $query->where('email', 'like', '%'.$request->email.'%');
            })
            ->when($request->filled('role') && $request->role !== '0', function ($query) use ($request) {
                $query->whereHas('roles', function ($subQuery) use ($request) {
                    $subQuery->where('name', $request->role);
                });
            })
            ->when(auth()->id(), function ($query) {
                $query->where('id', '<>', auth()->id());
            })
            ->orderByDesc('last_activation_at')
            ->get()
            ->map(fn (User $item) => $item->append('last_activation'));

        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    public function create(Request $request)
    {
        $roles = Role::all();

        if ($request->isMethod('get')) {
            return view('users.add', compact('roles'));
        }

        $credentials = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'exists:roles,id'],
            'password' => ['min:10', 'required_with:password_confirmation', 'same:password_confirmation'],
            'password_confirmation' => ['min:10'],
        ], [
            'email' => 'Поле имеет неправильный формат',
            'required' => 'Это поле обязательно',
            'unique' => 'Пользователь с такой почтой уже существует',
            'same' => 'Пароли должны совпадать',
            'required_with' => 'Заполните оба поля',
            'min' => 'Минимальная длина пароля — 10 символов',
        ]);

        $user = User::create([
            'name' => $credentials['name'],
            'email' => $credentials['email'],
            'password' => Hash::make($credentials['password']),
            'active' => true,
        ]);
        $user->assignRole([(int) $credentials['role']]);

        return redirect()
            ->route('users.show', ['id' => $user->id])
            ->with('success', 'Пользователь успешно добавлен!');
    }

    public function show(int $id)
    {
        $user = User::with('roles')->find($id);
        $logs = $user?->logs()->orderByDesc('created_at')->get() ?? collect();

        return view('users.show', compact('user', 'logs'));
    }

    public function toggleBanUser(int $id, Request $request)
    {
        $user = User::query()->findOrFail($id);

        if ($user->hasRole('admin')) {
            return redirect()->back()->withErrors(['ban' => 'Нельзя блокировать администратора']);
        }

        $shouldUnban = $request->routeIs('users.unban');
        $user->update(['active' => $shouldUnban]);

        return redirect()->back()->with([
            'success' => $shouldUnban
                ? 'Пользователь разблокирован'
                : 'Пользователь заблокирован',
        ]);
    }
}
