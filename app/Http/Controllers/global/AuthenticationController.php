<?php

namespace App\Http\Controllers\global;

use App\Http\Controllers\Controller;
use App\Http\Requests\global\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class AuthenticationController extends Controller
{
    public function loginShow(): Response
    {
        return Inertia::render('global/pages/auth/Login');
    }

    public function login(LoginRequest $request): HttpFoundationResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Force full page reload
        return Inertia::location(url('/'));
    }

    public function logout(Request $request): HttpFoundationResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Force full page reload
        return Inertia::location(route('login.show'));
    }
}
