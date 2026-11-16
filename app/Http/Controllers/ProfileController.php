<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLogger;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->isSuperAdmin() && ! $user->anotherSuperAdministratorExists()) {
            return back()->withErrors([
                'password' => 'This account was not deleted because it is the last Super Administrator.',
            ], 'userDeletion');
        }

        $blocked = $user->deletionBlockers();

        if ($blocked !== []) {
            return back()->withErrors([
                'password' => 'This account was not deleted because it still has '.implode(', ', $blocked).'.',
            ], 'userDeletion');
        }

        DB::transaction(function () use ($user) {
            AuditLogger::log(
                'Deleted',
                'User',
                'Deleted user: '.$user->name
            );

            $user->delete();
        });

        // Logout saves a remember token. Clear it first so the deleted account is not created again.
        $user->setRememberToken(null);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
