<?php

namespace App\Http\Controllers\global;

use App\Http\Controllers\Controller;
use App\Http\Requests\global\ProfilePasswordUpdateRequest;
use App\Http\Requests\global\ProfilePersonalDataUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return inertia('global/pages/profile/Edit', [
            // Refetched after 'updatePersonalData' and 'updatePassword'
            'record' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     *
     * Inertia post request
     */
    public function updatePersonalData(ProfilePersonalDataUpdateRequest $request): RedirectResponse
    {
        $request->user()->updateProfilePersonalData($request);

        return redirect()->back();
    }

    /**
     * Update the user`s password
     *
     * Inertia post request
     */
    public function updatePassword(ProfilePasswordUpdateRequest $request): RedirectResponse
    {
        // Update password
        auth()->user()->updateProfilePassword($request);

        // Logout user after password change
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to login page
        return redirect()->route('login', ['logged_out' => true]);
    }
}
