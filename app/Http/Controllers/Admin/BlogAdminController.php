<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BlogAdminController extends Controller {
    public function index(): Response {
        $posts = BlogPost::latest()->get()->map(fn($p) => [
            'id' => $p->id, 'title' => $p->title, 'slug' => $p->slug,
            'category' => $p->category, 'category_label' => $p->category_label,
            'status' => $p->status, 'author_name' => $p->author_name,
            'published_at' => $p->published_at?->format('d/m/Y') ?? '—',
            'created_at' => $p->created_at->format('d/m/Y'),
        ]);
        return Inertia::render('Admin/Blog/Index', ['posts' => $posts]);
    }

    public function create(): Response {
        return Inertia::render('Admin/Blog/Form', ['post' => null]);
    }

    public function store(Request $request): RedirectResponse {
        $data = $request->validate([
            'title'            => 'required|string|max:200',
            'excerpt'          => 'nullable|string|max:400',
            'content'          => 'required|string|min:50',
            'category'         => 'required|in:actualites,tutoriels,produit,entreprise',
            'author_name'      => 'required|string|max:100',
            'status'           => 'required|in:draft,published',
            'meta_title'       => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
        ]);
        $data['slug'] = Str::slug($data['title']).'-'.Str::random(6);
        if ($data['status'] === 'published') $data['published_at'] = now();
        BlogPost::create($data);
        return redirect()->route('admin.blog.index')->with('success', 'Article créé.');
    }

    public function edit(BlogPost $post): Response {
        return Inertia::render('Admin/Blog/Form', ['post' => $post]);
    }

    public function update(Request $request, BlogPost $post): RedirectResponse {
        $data = $request->validate([
            'title'            => 'required|string|max:200',
            'excerpt'          => 'nullable|string|max:400',
            'content'          => 'required|string|min:50',
            'category'         => 'required|in:actualites,tutoriels,produit,entreprise',
            'author_name'      => 'required|string|max:100',
            'status'           => 'required|in:draft,published',
            'meta_title'       => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
        ]);
        if ($data['status'] === 'published' && !$post->published_at) {
            $data['published_at'] = now();
        }
        $post->update($data);
        return redirect()->route('admin.blog.index')->with('success', 'Article mis à jour.');
    }

    public function destroy(BlogPost $post): RedirectResponse {
        $post->delete();
        return redirect()->route('admin.blog.index')->with('success', 'Article supprimé.');
    }
}
