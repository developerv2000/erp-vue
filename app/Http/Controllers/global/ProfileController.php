<?php

namespace App\Http\Controllers\global;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfilePasswordUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request)
    {
        return inertia('global/Profile', [
            'record' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->updateProfile($request);

        return redirect()->route('profile.edit');
    }

    /**
     * Update the user`s password
     */
    public function updatePassword(ProfilePasswordUpdateRequest $request): RedirectResponse
    {
        $request->user()->updateProfilePassword($request);

        return back()->with('status', 'password-updated');
    }
}
