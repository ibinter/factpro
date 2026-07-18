<?php

namespace App\Services;

use App\Models\ApprovalStep;
use App\Models\ApprovalWorkflow;
use App\Models\Document;
use App\Models\User;

class ApprovalService
{
    /**
     * Soumet un document au circuit de validation.
     * Crée les ApprovalStep pour chaque approbateur du workflow.
     */
    public function submitForApproval(Document $document, ApprovalWorkflow $workflow, User $submitter): void
    {
        $document->update([
            'approval_status' => 'pending_approval',
            'approval_workflow_id' => $workflow->id,
        ]);

        $approvers = $workflow->approvers ?? [];
        foreach ($approvers as $index => $approverId) {
            ApprovalStep::create([
                'document_id' => $document->id,
                'workflow_id' => $workflow->id,
                'step_number' => $index + 1,
                'approver_id' => $approverId,
                'status' => 'pending',
            ]);
        }
    }

    /**
     * Approuve une étape. Si c'est la dernière → document 'approved'.
     */
    public function approve(ApprovalStep $step, User $approver, string $comment = ''): void
    {
        $step->update([
            'status' => 'approved',
            'comment' => $comment,
            'decided_at' => now(),
        ]);

        $nextStep = ApprovalStep::where('document_id', $step->document_id)
            ->where('step_number', $step->step_number + 1)
            ->where('status', 'pending')
            ->first();

        if ($nextStep) {
            // Notifie le prochain approbateur (notification peut être ajoutée ici)
        } else {
            // Toutes les étapes approuvées → document approved
            $step->document->update(['approval_status' => 'approved']);
        }
    }

    /**
     * Rejette une étape → document 'rejected'.
     */
    public function reject(ApprovalStep $step, User $approver, string $comment): void
    {
        $step->update([
            'status' => 'rejected',
            'comment' => $comment,
            'decided_at' => now(),
        ]);

        $step->document->update(['approval_status' => 'rejected']);
    }

    /**
     * Délègue une étape à un autre utilisateur.
     */
    public function delegate(ApprovalStep $step, User $from, User $to): void
    {
        $step->update([
            'status' => 'delegated',
            'delegated_to_id' => $to->id,
        ]);

        // Crée une nouvelle étape identique pour $to
        ApprovalStep::create([
            'document_id' => $step->document_id,
            'workflow_id' => $step->workflow_id,
            'step_number' => $step->step_number,
            'approver_id' => $to->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Retourne les étapes en attente pour un utilisateur.
     */
    public function pendingForUser(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return ApprovalStep::where(function ($q) use ($user) {
            $q->where('approver_id', $user->id)
              ->orWhere('delegated_to_id', $user->id);
        })
            ->where('status', 'pending')
            ->with(['document.customer', 'document.company'])
            ->get();
    }
}
