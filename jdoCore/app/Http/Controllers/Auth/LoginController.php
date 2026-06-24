<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Roles that should land on the admin dashboard after auth.
     */
    protected array $adminRoles = ['admin', 'superadmin', 'owner', 'staff'];

    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectAfterAuth();
        }
        return view('theme::auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Merge guest cart for regular customers only.
            if (!Auth::user()->hasAnyRole($this->adminRoles)) {
                app(\App\Services\CartService::class)->mergeGuestCart();
            }

            return $this->redirectAfterAuth();
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * Send the user to the right home based on their role.
     */
    protected function redirectAfterAuth()
    {
        $user = Auth::user();

        if ($user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole($this->adminRoles)) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended(route('home'));
    }
}
