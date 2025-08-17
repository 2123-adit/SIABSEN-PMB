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
            'user_id' => 'required',
            'password' => 'required',
        ]);

        $credentials = [
            'user_id' => $request->user_id,
            'password' => $request->password,
            'status' => 'aktif',
            'role' => 'admin'
        ];

        // Log login attempts
        Log::info('Login attempt', [
            'user_id' => $request->user_id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Log successful login
            Log::info('Login successful', [
                'user_id' => Auth::id(),
                'user_id_field' => Auth::user()->user_id,
                'ip' => $request->ip()
            ]);
            
            return redirect()->intended(route('admin.dashboard'));
        }

        // Log failed login
        Log::warning('Login failed', [
            'user_id' => $request->user_id,
            'ip' => $request->ip()
        ]);

        return back()->withErrors([
            'user_id' => 'User ID atau password salah.',
        ])->onlyInput('user_id');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}