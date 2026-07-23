<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Help/Index');
    }

    public function article(Request $request, string $slug): Response
    {
        return Inertia::render('Help/Article', ['slug' => $slug]);
    }
}
