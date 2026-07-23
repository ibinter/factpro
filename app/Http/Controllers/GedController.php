<?php

namespace App\Http\Controllers;

use App\Models\GedDocument;
use App\Models\GedFolder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GedController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        // Folders tree (root folders with children eager-loaded)
        $folders = GedFolder::where('company_id', $company->id)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        $query = GedDocument::where('company_id', $company->id)
            ->with(['folder', 'uploader']);

        if ($request->filled('folder_id')) {
            if ($request->folder_id === 'null') {
                $query->whereNull('ged_folder_id');
            } else {
                $query->where('ged_folder_id', $request->folder_id);
            }
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('tag')) {
            $query->whereRaw("JSON_CONTAINS(tags, ?)", [json_encode($request->tag)]);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $documents = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Ged/Index', [
            'folders'   => $folders,
            'documents' => $documents,
            'filters'   => $request->only(['folder_id', 'category', 'tag', 'search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file'          => 'required|file|max:102400', // 100 MB
            'title'         => 'required|string|max:255',
            'category'      => 'nullable|string|max:50',
            'tags'          => 'nullable|array',
            'tags.*'        => 'string|max:50',
            'ged_folder_id' => 'nullable|exists:ged_folders,id',
        ]);

        $company = $request->user()->currentCompany;
        $file    = $request->file('file');
        $path    = $file->store("ged/{$company->id}", 'local');

        // Extract text for PDFs (basic)
        $contentText = null;
        if ($file->getMimeType() === 'application/pdf') {
            $contentText = @file_get_contents($file->getRealPath());
            // Strip binary PDF to extract printable text only
            $contentText = preg_replace('/[^\x20-\x7E\n\r\t]/', ' ', $contentText ?? '');
            $contentText = mb_substr($contentText, 0, 65535);
        }

        GedDocument::create([
            'company_id'    => $company->id,
            'ged_folder_id' => $request->ged_folder_id,
            'title'         => $request->title,
            'category'      => $request->category,
            'tags'          => $request->tags ?? [],
            'file_path'     => $path,
            'file_name'     => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'content_text'  => $contentText,
            'uploaded_by'   => $request->user()->id,
        ]);

        return back()->with('success', 'Document ajouté à la GED.');
    }

    public function update(Request $request, GedDocument $gedDocument): RedirectResponse
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'category'      => 'nullable|string|max:50',
            'tags'          => 'nullable|array',
            'tags.*'        => 'string|max:50',
            'ged_folder_id' => 'nullable|exists:ged_folders,id',
        ]);

        $gedDocument->update($request->only(['title', 'category', 'tags', 'ged_folder_id']));

        return back()->with('success', 'Document mis à jour.');
    }

    public function destroy(GedDocument $gedDocument): RedirectResponse
    {
        Storage::disk('local')->delete($gedDocument->file_path);
        $gedDocument->delete();

        return back()->with('success', 'Document supprimé.');
    }

    public function createFolder(Request $request): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'parent_id'  => 'nullable|exists:ged_folders,id',
            'color'      => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $company = $request->user()->currentCompany;

        GedFolder::create([
            'company_id' => $company->id,
            'parent_id'  => $request->parent_id,
            'name'       => $request->name,
            'color'      => $request->color ?? '#6B7280',
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return back()->with('success', 'Dossier créé.');
    }

    public function deleteFolder(GedFolder $gedFolder): RedirectResponse
    {
        // Move all documents in this folder to no folder
        GedDocument::where('ged_folder_id', $gedFolder->id)
            ->update(['ged_folder_id' => null]);

        // Recursively move child folders' documents too
        foreach ($gedFolder->children as $child) {
            GedDocument::where('ged_folder_id', $child->id)
                ->update(['ged_folder_id' => null]);
            $child->delete();
        }

        $gedFolder->delete();

        return back()->with('success', 'Dossier supprimé.');
    }

    public function download(GedDocument $gedDocument): StreamedResponse
    {
        return Storage::disk('local')->download(
            $gedDocument->file_path,
            $gedDocument->file_name
        );
    }
}
