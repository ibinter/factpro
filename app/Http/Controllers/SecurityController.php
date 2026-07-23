<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class SecurityController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Public/Security', [
            'canLogin'    => \Illuminate\Support\Facades\Route::has('login'),
            'canRegister' => \Illuminate\Support\Facades\Route::has('register'),
        ]);
    }
}
