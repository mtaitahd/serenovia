<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SetupController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (User::where('is_admin', true)->exists()) {
            return redirect()->route('admin.login');
        }

        return view('admin.setup');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(User::where('is_admin', true)->exists(), 403, 'Admin setup has already been completed.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        $admin = User::create([...$data, 'is_admin' => true]);
        Auth::login($admin);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')->with('success', 'Admin account created. Welcome to Serenovia.');
    }
}
