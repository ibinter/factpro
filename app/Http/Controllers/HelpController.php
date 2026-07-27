<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;

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

    public function guidePdf(): HttpResponse
    {
        $pdf = Pdf::loadView('pdf.guide')
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'      => 'DejaVu Sans',
                'isRemoteEnabled'  => false,
                'isHtml5ParserEnabled' => true,
                'dpi'              => 150,
                'defaultMediaType' => 'print',
            ]);

        return $pdf->download('guide-utilisateur-factpro.pdf');
    }
}
