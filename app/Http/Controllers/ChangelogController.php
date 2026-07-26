<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class ChangelogController extends Controller
{
    public function index(): \Inertia\Response
    {
        return Inertia::render('Changelog/Index');
    }

    public function public(): \Inertia\Response
    {
        $versions = [];

        try {
            $versions = \App\Models\AppVersion::published()->latest()->get()->toArray();
        } catch (\Exception $e) {
            // Model may not exist yet — fallback to empty (Vue uses static data)
            $versions = [];
        }

        return Inertia::render('Public/Changelog', [
            'versions' => $versions,
        ]);
    }
}
