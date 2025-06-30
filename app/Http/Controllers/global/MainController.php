<?php

namespace App\Http\Controllers\global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MainController extends Controller
{
    public function redirectToHomePage(Request $request)
    {
        $homePage = $request->user()->detectHomeRouteName();

        return redirect()->to($homePage);
    }
}
