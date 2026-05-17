<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app-blank')]
#[Title('Log Masuk')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected array $rules = [
        'email'    => 'required|email',
        'password' => 'required|min:6',
    ];

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();

            $user = Auth::user();

            return match ($user->role) {
                'superadmin' => redirect()->route('superadmin.dashboard'),
                'surveyor'   => redirect()->route('surveyor.dashboard'),
                'engineer'   => redirect()->route('engineer.dashboard'),
                default      => redirect('/'),
            };
        }

        $this->addError('email', 'E-mel atau kata laluan tidak sah.');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
