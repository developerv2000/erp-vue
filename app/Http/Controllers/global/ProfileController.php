<?php

namespace App\Http\Controllers\global;

use App\Http\Controllers\Controller;
use App\Http\Requests\global\ProfilePasswordUpdateRequest;
use App\Http\Requests\global\ProfilePersonalDataUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request)
    {
        return inertia('global/pages/profile/Edit', [
            'record' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function updatePersonalData(ProfilePersonalDataUpdateRequest $request): RedirectResponse
    {
        $request->user()->updateProfilePersonalData($request);

        return redirect()->back();
    }

    /**
     * Update the user`s password
     */
    public function updatePassword(ProfilePasswordUpdateRequest $request): RedirectResponse
    {
        $request->user()->updateProfilePassword($request);

        return redirect()->back();
    }
}
