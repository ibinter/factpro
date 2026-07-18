<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\EmailTracking;
use App\Services\EmailTrackingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

class EmailTrackingController extends Controller
{
    public function __construct(private EmailTrackingService $service) {}

    /**
     * Pixel de tracking — enregistre l'ouverture, retourne un GIF 1x1 transparent.
     * PAS d'auth (appelé depuis le client email du destinataire).
     */
    public function trackOpen(Request $request, string $token): Response
    {
        $tracking = EmailTracking::where('tracking_token', $token)->first();

        if ($tracking) {
            $this->service->recordOpen($tracking, $request);
        }

        // GIF 1x1 pixel transparent (43 octets)
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($gif, 200, [
            'Content-Type'  => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
        ]);
    }

    /**
     * Clic tracké — enregistre le clic et redirige vers l'URL originale.
     * PAS d'auth.
     */
    public function trackClick(Request $request, string $token)
    {
        $tracking = EmailTracking::where('tracking_token', $token)->first();

        if ($tracking) {
            $this->service->recordClick($tracking);
        }

        $url = urldecode($request->query('url', '/'));

        return redirect()->away($url);
    }

    /**
     * Dashboard engagement email (Inertia, auth+license).
     */
    public function dashboard(Request $request)
    {
        $companyId = $request->user()->current_company_id;
        $stats = $this->service->getStats($companyId, 30);

        $trackings = EmailTracking::where('company_id', $companyId)
            ->with('document:id,number,type,total')
            ->latest('sent_at')
            ->limit(100)
            ->get();

        return Inertia::render('EmailTracking/Dashboard', [
            'stats'     => $stats,
            'trackings' => $trackings,
        ]);
    }

    /**
     * Tracking JSON pour un document (auth).
     */
    public function documentTracking(Request $request, Document $document)
    {
        abort_unless($document->company_id === $request->user()->current_company_id, 403);

        $trackings = EmailTracking::where('document_id', $document->id)
            ->orderByDesc('sent_at')
            ->get();

        return response()->json(['trackings' => $trackings]);
    }

    /**
     * Stats globales JSON (auth).
     */
    public function stats(Request $request)
    {
        $companyId = $request->user()->current_company_id;
        $days = (int) $request->query('days', 30);

        return response()->json($this->service->getStats($companyId, $days));
    }
}
