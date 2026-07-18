<?php

namespace App\Http\Controllers;

use App\Services\LicenseService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApiDocsController extends Controller
{
    public function __construct(private LicenseService $licenses)
    {
    }

    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $license = $this->licenses->currentFor($user);
        $hasBusiness = $license && in_array($license->plan->code ?? '', ['business', 'enterprise']);

        return Inertia::render('ApiDocs/Index', [
            'hasBusiness' => $hasBusiness,
        ]);
    }
}
