<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
            'status' => 'aktif',
            'role' => 'admin'
        ];

        // Log login attempts
        Log::info('Login attempt', [
            'username' => $request->username,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Log successful login
            Log::info('Login successful', [
                'user_id' => Auth::id(),
                'username' => Auth::user()->username,
                'ip' => $request->ip()
            ]);
            
            return redirect()->intended(route('admin.dashboard'));
        }

        // Log failed login
        Log::warning('Login failed', [
            'username' => $request->username,
            'ip' => $request->ip()
        ]);

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}