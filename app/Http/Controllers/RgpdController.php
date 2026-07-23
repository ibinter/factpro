<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use ZipArchive;

class RgpdController extends Controller
{
    public function show(Request $request): \Inertia\Response
    {
        $user = $request->user();

        return Inertia::render('Profile/Rgpd', [
            'user' => [
                'name'       => $user->name,
                'email'      => $user->email,
                'created_at' => $user->created_at->format('d/m/Y'),
            ],
            'deletion_requested' => session('deletion_requested', false),
        ]);
    }

    public function export(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $user    = $request->user();
        $company = $user->currentCompany ?? $user->companies()->first();

        $data = [
            'compte' => [
                'id'         => $user->id,
                'nom'        => $user->name,
                'email'      => $user->email,
                'telephone'  => $user->phone ?? null,
                'pays'       => $user->country ?? null,
                'cree_le'    => $user->created_at->toIso8601String(),
                'modifie_le' => $user->updated_at->toIso8601String(),
            ],
        ];

        if ($company) {
            $data['societe'] = [
                'nom'     => $company->name,
                'siret'   => $company->siret ?? null,
                'adresse' => $company->address ?? null,
                'email'   => $company->email ?? null,
                'tel'     => $company->phone ?? null,
            ];

            $data['documents'] = DB::table('documents')
                ->where('company_id', $company->id)
                ->select('id', 'type', 'number', 'status', 'total', 'currency', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();

            $data['clients'] = DB::table('customers')
                ->where('company_id', $company->id)
                ->select('id', 'name', 'email', 'phone', 'city', 'country', 'created_at')
                ->get()
                ->toArray();

            $data['produits'] = DB::table('products')
                ->where('company_id', $company->id)
                ->select('id', 'name', 'price', 'unit', 'tax_rate', 'created_at')
                ->get()
                ->toArray();
        }

        $data['licences'] = DB::table('licenses')
            ->where('user_id', $user->id)
            ->select('id', 'plan_id', 'status', 'type', 'starts_at', 'ends_at', 'created_at')
            ->get()
            ->toArray();

        $data['nps'] = DB::table('nps_responses')
            ->where('user_id', $user->id)
            ->select('score', 'comment', 'created_at')
            ->get()
            ->toArray();

        // Créer les fichiers dans un répertoire temporaire
        $tmpDir = sys_get_temp_dir() . '/factpro_export_' . $user->id . '_' . time();
        mkdir($tmpDir, 0755, true);

        // JSON principal
        file_put_contents(
            $tmpDir . '/mes_donnees.json',
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        // CSV documents
        if (!empty($data['documents'])) {
            $csv = "ID,Type,Numéro,Statut,Total,Devise,Date\n";
            foreach ($data['documents'] as $d) {
                $row = (array) $d;
                $csv .= "\"{$row['id']}\",\"{$row['type']}\",\"{$row['number']}\",\"{$row['status']}\",\"{$row['total']}\",\"{$row['currency']}\",\"{$row['created_at']}\"\n";
            }
            file_put_contents($tmpDir . '/documents.csv', $csv);
        }

        // CSV clients
        if (!empty($data['clients'])) {
            $csv = "ID,Nom,Email,Téléphone,Ville,Pays,Date\n";
            foreach ($data['clients'] as $c) {
                $row = (array) $c;
                $csv .= "\"{$row['id']}\",\"{$row['name']}\",\"{$row['email']}\",\"{$row['phone']}\",\"{$row['city']}\",\"{$row['country']}\",\"{$row['created_at']}\"\n";
            }
            file_put_contents($tmpDir . '/clients.csv', $csv);
        }

        // Créer le ZIP
        $zipPath = sys_get_temp_dir() . '/factpro_mes_donnees_' . $user->id . '.zip';
        $zip     = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach (glob($tmpDir . '/*') as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();

        // Nettoyer le répertoire temporaire
        array_map('unlink', glob($tmpDir . '/*'));
        rmdir($tmpDir);

        $zipContent = file_get_contents($zipPath);
        unlink($zipPath);

        return response($zipContent, 200, [
            'Content-Type'        => 'application/zip',
            'Content-Disposition' => 'attachment; filename="factpro_mes_donnees_' . date('Y-m-d') . '.zip"',
        ]);
    }

    public function requestDeletion(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['password' => 'required|current_password']);

        $user = $request->user();

        try {
            Mail::raw(
                "Demande de suppression de compte FactPro\n\n" .
                "Utilisateur : {$user->name} ({$user->email})\n" .
                "ID : {$user->id}\n" .
                "Date de la demande : " . now()->format('d/m/Y H:i') . "\n\n" .
                "Conformément au RGPD (art. 17), cet utilisateur demande la suppression de toutes ses données.\n" .
                "Délai légal de traitement : 30 jours.",
                fn($m) => $m->to('dpo@ibigsoft.com')
                    ->subject("[FactPro RGPD] Demande de suppression — {$user->email}")
            );

            Mail::raw(
                "Bonjour {$user->name},\n\n" .
                "Nous avons bien reçu votre demande de suppression de compte FactPro.\n\n" .
                "Conformément au RGPD (article 17 — droit à l'effacement), nous traiterons votre demande dans un délai maximum de 30 jours.\n\n" .
                "Vous recevrez une confirmation par email une fois votre compte et vos données supprimés.\n\n" .
                "Si vous avez changé d'avis, contactez-nous à support@ibigsoft.com avant le traitement de votre demande.\n\n" .
                "Cordialement,\nL'équipe IBIG Soft",
                fn($m) => $m->to($user->email)
                    ->subject("[FactPro] Demande de suppression de compte reçue")
            );
        } catch (\Throwable) {
        }

        return back()->with('deletion_requested', true);
    }
}
