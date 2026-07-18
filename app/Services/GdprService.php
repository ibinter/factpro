<?php

namespace App\Services;

use App\Models\DocumentAuditLog;
use App\Models\User;
use Illuminate\Support\Collection;

class GdprService
{
    /**
     * Exporte toutes les données personnelles de l'utilisateur (RGPD Art. 20).
     */
    public function exportData(User $user): array
    {
        $companies = $user->companies()->get(['companies.id', 'companies.name', 'company_user.role']);

        $companyIds = $companies->pluck('id');

        $auditLogs = DocumentAuditLog::where('user_id', $user->id)
            ->with('document:id,number,type,company_id', 'company:id,name')
            ->orderByDesc('created_at')
            ->limit(500)
            ->get();

        return [
            'exported_at' => now()->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'country' => $user->country,
                'locale' => $user->locale,
                'created_at' => $user->created_at?->toIso8601String(),
            ],
            'companies' => $companies->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'role' => $c->pivot->role,
            ])->values()->all(),
            'audit_logs' => $auditLogs->map(fn ($log) => [
                'id' => $log->id,
                'event' => $log->event,
                'document_number' => $log->document?->number,
                'document_type' => $log->document?->type,
                'company_name' => $log->company?->name,
                'created_at' => $log->created_at?->toIso8601String(),
                'meta' => $log->meta,
            ])->values()->all(),
        ];
    }

    /**
     * Supprime les données personnelles de l'utilisateur (RGPD Art. 17 — droit à l'oubli).
     * Anonymise les logs, supprime le compte.
     */
    public function deleteAccount(User $user): void
    {
        // Anonymiser les logs d'audit (nullification de user_id déjà gérée par cascadeOnDelete/nullOnDelete)
        // Les documents appartiennent aux sociétés, pas à l'utilisateur — on les conserve
        // Détacher l'utilisateur de toutes les sociétés
        $user->companies()->detach();

        // Supprimer les tokens API
        $user->tokens()->delete();

        // Supprimer l'utilisateur (les audit logs seront mis à NULL via nullOnDelete)
        $user->delete();
    }

    /**
     * Retourne les entrées du journal d'audit pour la société courante de l'utilisateur.
     */
    public function auditLogForCompany(User $user, int $perPage = 50): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $companyId = $user->current_company_id;

        return DocumentAuditLog::where('company_id', $companyId)
            ->with(['user:id,name,email', 'document:id,number,type'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
