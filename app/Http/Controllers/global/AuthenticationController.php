<?php

namespace App\Http\Controllers\global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthenticationController extends Controller
{
    public function loginShow(Request $request)
    {
        return inertia('auth/Login');
    }
}
