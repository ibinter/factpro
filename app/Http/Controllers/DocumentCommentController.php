<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocumentCommentController extends Controller
{
    public function store(Request $request, Document $document): RedirectResponse
    {
        // Vérifier que l'utilisateur appartient à la même entreprise
        abort_unless(
            $request->user()->currentCompany?->id === $document->company_id,
            403
        );

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $document->comments()->create([
            'user_id' => $request->user()->id,
            'body'    => $validated['body'],
        ]);

        return back()->with('success', 'Commentaire ajouté.');
    }

    public function destroy(Request $request, Document $document, DocumentComment $comment): RedirectResponse
    {
        abort_unless(
            $request->user()->currentCompany?->id === $document->company_id,
            403
        );

        // Seul l'auteur peut supprimer son commentaire
        abort_unless($comment->user_id === $request->user()->id, 403);

        $comment->delete();

        return back()->with('success', 'Commentaire supprimé.');
    }
}
