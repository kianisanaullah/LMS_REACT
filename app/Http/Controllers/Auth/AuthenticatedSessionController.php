<?php 

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('auth/login', [
            'canResetPassword' => true,
            'status' => $request->session()->get('status'),
        ]);
    }

    public function store(Request $request)
    {
        
       
        $request->validate([
            'EMAIL'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        Log::info('Login attempt started', [
            'email' => $request->EMAIL,
        ]);

        if (Auth::attempt([
            'EMAIL'    => $request->EMAIL,
            'password' => $request->password,
        ])) {
            Log::info('Login successful', [
                'email' => $request->EMAIL,
            ]);
            
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        Log::warning('Login failed', [
            'email' => $request->EMAIL,
        ]);


        return back()->withErrors([
            'EMAIL' => 'Invalid credentials.',
        ]);
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('User logged out');

        return redirect()->route('login');
    }
}
