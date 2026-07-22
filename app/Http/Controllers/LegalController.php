<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    private function sharedProps(): array
    {
        return [
            'canLogin'    => \Illuminate\Support\Facades\Route::has('login'),
            'canRegister' => \Illuminate\Support\Facades\Route::has('register'),
        ];
    }

    public function mentions(): Response
    {
        return Inertia::render('Legal/Mentions', $this->sharedProps());
    }

    public function cgu(): Response
    {
        return Inertia::render('Legal/Cgu', $this->sharedProps());
    }

    public function confidentialite(): Response
    {
        return Inertia::render('Legal/Confidentialite', $this->sharedProps());
    }

    public function cookies(): Response
    {
        return Inertia::render('Legal/Cookies', $this->sharedProps());
    }

    public function pi(): Response
    {
        return Inertia::render('Legal/Pi', $this->sharedProps());
    }

    public function resiliation(): Response
    {
        return Inertia::render('Legal/Resiliation', $this->sharedProps());
    }
}
