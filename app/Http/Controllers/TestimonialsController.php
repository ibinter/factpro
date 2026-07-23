<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class TestimonialsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Public/Testimonials', [
            'canLogin'    => \Illuminate\Support\Facades\Route::has('login'),
            'canRegister' => \Illuminate\Support\Facades\Route::has('register'),
        ]);
    }
}
