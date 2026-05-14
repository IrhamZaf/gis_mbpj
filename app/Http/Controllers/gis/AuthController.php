<?php

namespace App\Http\Controllers\gis;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        $pageConfigs = ['myLayout' => 'blank'];

        return view('auth.login', ['pageConfigs' => $pageConfigs]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);
        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();
        if (! in_array($user->role, [User::ROLE_ADMIN, User::ROLE_SURVEYOR, User::ROLE_ENGINEER], true)) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Akaun tidak mempunyai peranan GIS yang sah.',
            ]);
        }

        return redirect()->intended(url('/'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
