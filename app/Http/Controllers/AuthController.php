<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Check if already logged in
        if (session('user_id')) {
            return redirect('/');
        }
        
        return view('auth.login');
    }
    
    public function processLogin(Request $request)
    {
        $email = trim($request->input('email', ''));
        $password = $request->input('password', '');
        
        // Basic validation
        if (empty($email) || empty($password)) {
            return back()->with('error', 'Email dan password harus diisi!');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return back()->with('error', 'Format email tidak valid!');
        }
        
        // Rate limiting check
        $attempt_key = 'login_attempts_' . md5($email);
        $attempts = session($attempt_key, 0);
        
        if ($attempts >= 5) {
            return back()->with('error', 'Terlalu banyak percobaan login. Silakan coba lagi dalam 15 menit.');
        }
        
        try {
            // Case-insensitive email match
            $user = DB::table('users')
                ->whereRaw('LOWER(email) = ?', [strtolower($email)])
                ->first();
            
            if ($user && password_verify($password, $user->password)) {
                // Reset login attempts
                session()->forget($attempt_key);
                
                // Set session
                session([
                    'user_id' => $user->id,
                    'user_name' => $user->display_name ?? $user->full_name ?? 'User',
                    'user_email' => $user->email,
                    'user_role' => $user->role ?? 'user',
                    'logged_in' => true
                ]);
                
                // Log successful login
                DB::table('logs')->insert([
                    'user_id' => $user->id,
                    'action' => 'login',
                    'description' => 'User logged in successfully',
                    'ip' => $request->ip(),
                    'created_at' => now()
                ]);
                
                return redirect()->intended('/');
            } else {
                // Increment login attempts
                session([$attempt_key => $attempts + 1]);
                
                return back()->with('error', 'Email atau password salah!');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }
    
    public function logout()
    {
        $user_id = session('user_id');
        
        // Log logout
        if ($user_id) {
            try {
                DB::table('logs')->insert([
                    'user_id' => $user_id,
                    'action' => 'logout',
                    'description' => 'User logged out',
                    'ip' => request()->ip(),
                    'created_at' => now()
                ]);
            } catch (\Exception $e) {
                // Silent fail for logging
            }
        }
        
        // Clear session
        session()->flush();
        
        return redirect('/login')->with('success', 'Anda telah berhasil logout.');
    }
}