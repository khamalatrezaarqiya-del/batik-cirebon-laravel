<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password and invalidate other active sessions.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();
        $hashed = Hash::make($validated['password']);

        $user->forceFill([
            'password' => $hashed,
            'remember_token' => Str::random(60),
        ])->save();

        $request->session()->regenerate();
        $request->session()->put('password_hash_web', $user->getAuthPassword());

        $sessionTable = config('session.table', 'sessions');
        if (Schema::hasTable($sessionTable)) {
            DB::table($sessionTable)
                ->where('user_id', $user->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        return back();
    }
}
