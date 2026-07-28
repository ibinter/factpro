<?php

namespace App\Http\Controllers;

use App\Models\WhatsappMessage;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WhatsAppController extends Controller
{
    public function webhook(Request $request)
    {
        $company = \App\Models\Company::first(); // Webhook global, on prend la première company ou on identifie via paramètre

        if ($request->isMethod('get')) {
            if (
                $request->get('hub_mode') === 'subscribe' &&
                $request->get('hub_verify_token') === ($company->wa_verify_token ?? null)
            ) {
                return response($request->get('hub_challenge'), 200);
            }
            abort(403);
        }

        // POST: réception d'événements Meta (livraison, lecture, etc.)
        \Log::info('WhatsApp webhook received', $request->all());

        // Traiter les mises à jour de statut
        $entries = $request->input('entry', []);
        foreach ($entries as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value = $change['value'] ?? [];
                foreach ($value['statuses'] ?? [] as $statusUpdate) {
                    $waMessageId = $statusUpdate['id'] ?? null;
                    $newStatus   = $statusUpdate['status'] ?? null;
                    if ($waMessageId && $newStatus) {
                        WhatsappMessage::where('wa_message_id', $waMessageId)
                            ->update(['status' => $newStatus]);
                    }
                }
            }
        }

        return response('OK', 200);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'to_phone'    => 'required|string',
            'message'     => 'required|string',
            'customer_id' => 'nullable|integer|exists:customers,id',
            'document_id' => 'nullable|integer|exists:documents,id',
        ]);

        $company = $request->user()->currentCompany;

        try {
            $wa     = new WhatsAppService($company);
            $result = $wa->sendText($request->to_phone, $request->message);

            $waMsg = WhatsappMessage::create([
                'company_id'   => $company->id,
                'customer_id'  => $request->customer_id,
                'document_id'  => $request->document_id,
                'to_phone'     => $request->to_phone,
                'message_type' => 'text',
                'body'         => $request->message,
                'status'       => 'sent',
                'wa_message_id'=> $result['messages'][0]['id'] ?? null,
                'sent_at'      => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message envoyé avec succès.',
                'data'    => $waMsg,
            ]);
        } catch (\Exception $e) {
            WhatsappMessage::create([
                'company_id'   => $company->id,
                'customer_id'  => $request->customer_id,
                'document_id'  => $request->document_id,
                'to_phone'     => $request->to_phone,
                'message_type' => 'text',
                'body'         => $request->message,
                'status'       => 'failed',
                'error_message'=> $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Échec de l\'envoi : ' . $e->getMessage(),
            ], 422);
        }
    }

    public function test(Request $request)
    {
        $company = $request->user()->currentCompany;

        if (! $company->wa_enabled || ! $company->wa_phone_number_id) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp non configuré ou désactivé.',
            ], 422);
        }

        $testPhone = $company->phone ?? null;
        if (! $testPhone) {
            return response()->json([
                'success' => false,
                'message' => 'Numéro de téléphone de la company non défini.',
            ], 422);
        }

        try {
            $wa     = new WhatsAppService($company);
            $result = $wa->sendText($testPhone, 'Test FactPro ✅ - Connexion WhatsApp Business opérationnelle');

            return response()->json([
                'success' => true,
                'message' => 'Message de test envoyé avec succès.',
                'data'    => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Échec du test : ' . $e->getMessage(),
            ], 422);
        }
    }

    public function history(Request $request)
    {
        $company  = $request->user()->currentCompany;
        $messages = WhatsappMessage::where('company_id', $company->id)
            ->with('customer')
            ->latest()
            ->paginate(20);

        return Inertia::render('WhatsApp/History', [
            'messages' => $messages,
        ]);
    }
}
