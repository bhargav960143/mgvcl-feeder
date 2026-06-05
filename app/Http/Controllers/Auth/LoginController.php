<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to($this->redirectPath());
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'employee_id' => ['required', 'string'],
            'password'    => ['required', 'string'],
        ]);

        $identifier = $request->employee_id;
        $remember   = $request->boolean('remember');

        $attempted = Auth::attempt(['employee_id' => $identifier, 'password' => $request->password], $remember)
            ?: Auth::attempt(['email' => $identifier, 'password' => $request->password], $remember);

        if ($attempted) {
            $request->session()->regenerate();
            return redirect()->intended($this->redirectPath());
        }

        return back()->withErrors([
            'employee_id' => 'Invalid employee ID / email or password.',
        ])->onlyInput('employee_id');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function redirectPath(): string
    {
        $user = Auth::user();

        if ($user && $user->hasAnyRole(['admin', 'circle'])) {
            return route('dashboard');
        }

        return route('feeders.index');
    }
}
