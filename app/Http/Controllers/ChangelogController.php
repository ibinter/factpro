<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class ChangelogController extends Controller
{
    public function index(): \Inertia\Response
    {
        return Inertia::render('Changelog/Index');
    }
}
