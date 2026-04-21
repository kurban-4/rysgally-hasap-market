<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $request->session()->regenerate();

            return match(Auth::user()->role) {
                'admin'     => redirect()->route('welcome'),
                'salesman'  => redirect()->route('sales.index'),
                'storage'   => redirect()->route('storage.index'),
                'wholesale' => redirect()->route('wholesale.index'),
                default     => redirect()->route('login'),
            };
        }

        return back()->withErrors(['username' => 'Неверный логин или пароль.'])->onlyInput('username');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}