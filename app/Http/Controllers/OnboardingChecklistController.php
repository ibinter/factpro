<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingChecklistController extends Controller
{
    public function status(Request $request): JsonResponse
    {
        $user    = $request->user();
        $company = $user->currentCompany;

        $steps = [
            [
                'id'          => 'company_logo',
                'title'       => 'Ajouter le logo de votre société',
                'description' => 'Personnalisez vos documents avec votre logo',
                'cta'         => 'Configurer',
                'route'       => '/settings/company',
                'icon'        => '🏢',
                'done'        => $company && !empty($company->logo),
            ],
            [
                'id'          => 'first_customer',
                'title'       => 'Créer votre premier client',
                'description' => 'Ajoutez un client pour commencer à facturer',
                'cta'         => 'Ajouter un client',
                'route'       => '/customers/create',
                'icon'        => '👤',
                'done'        => $company
                    ? \App\Models\Customer::where('company_id', $company->id)->exists()
                    : false,
            ],
            [
                'id'          => 'first_product',
                'title'       => 'Ajouter un produit ou service',
                'description' => 'Créez votre catalogue de produits',
                'cta'         => 'Ajouter un produit',
                'route'       => '/products/create',
                'icon'        => '📦',
                'done'        => $company
                    ? \App\Models\Product::where('company_id', $company->id)->exists()
                    : false,
            ],
            [
                'id'          => 'first_invoice',
                'title'       => 'Créer votre première facture',
                'description' => 'Générez et envoyez votre première facture',
                'cta'         => 'Créer une facture',
                'route'       => '/documents/create?type=invoice',
                'icon'        => '📄',
                'done'        => $company
                    ? \App\Models\Document::where('company_id', $company->id)
                        ->where('type', 'invoice')
                        ->exists()
                    : false,
            ],
            [
                'id'          => 'payment_method',
                'title'       => 'Configurer un moyen de paiement',
                'description' => 'Permettez à vos clients de payer en ligne',
                'cta'         => 'Configurer',
                'route'       => '/billing',
                'icon'        => '💳',
                'done'        => \App\Models\GatewayConfig::where('is_active', true)->exists()
                    || \App\Models\PaymentMethodConfig::where('is_active', true)->exists(),
            ],
            [
                'id'          => 'invite_team',
                'title'       => 'Inviter un membre de votre équipe',
                'description' => 'Collaborez avec votre équipe sur FactPro',
                'cta'         => 'Inviter',
                'route'       => '/team',
                'icon'        => '👥',
                'done'        => $company
                    ? \App\Models\User::where('company_id', $company->id)
                        ->where('id', '!=', $user->id)
                        ->exists()
                    : false,
            ],
            [
                'id'          => 'download_app',
                'title'       => "Installer l'application mobile",
                'description' => 'Accédez à FactPro depuis votre téléphone',
                'cta'         => 'Installer',
                'route'       => '/pwa-install',
                'icon'        => '📱',
                'done'        => false, // Étape manuelle — toujours false
            ],
        ];

        $done  = count(array_filter($steps, fn ($s) => $s['done']));
        $total = count($steps);

        return response()->json([
            'steps'    => $steps,
            'done'     => $done,
            'total'    => $total,
            'percent'  => $total > 0 ? round(($done / $total) * 100) : 0,
            'complete' => $done === $total,
        ]);
    }
}
