<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class MemberAccountRegistrationController extends Controller
{
    public function create()
    {
        return view('auth.member-account-register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:30',
            'nic' => 'nullable|string|max:40|unique:user_profiles,nic',
            'address' => 'nullable|string|max:1000',
            'date_of_birth' => 'required|date|before:today',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            UserProfile::create([
                'user_id' => $user->id,
                'phone' => $validated['phone'],
                'nic' => $validated['nic'] ?? null,
                'address' => $validated['address'] ?? null,
                'date_of_birth' => $validated['date_of_birth'],
            ]);

            if (Role::query()->where('name', 'Member')->exists()) {
                $user->assignRole('Member');
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('memberDashboard');
    }
}
