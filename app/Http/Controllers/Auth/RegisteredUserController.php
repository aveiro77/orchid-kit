<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Orchid\Platform\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'nomor_wa'     => $request->nomor_wa,
            'kota'         => $request->kota,
            'status_aktif' => true,
        ]);

        $role = Role::where('slug', 'anggota')->first();
        if ($role) {
            $user->roles()->attach($role);
        }

        Auth::login($user);

        return redirect()->route('platform.index');
    }
}
