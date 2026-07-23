<?php
namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller {
    public function index(): Response {
        $posts = BlogPost::published()->latest('published_at')->get()->map(fn($p) => [
            'id' => $p->id, 'title' => $p->title, 'slug' => $p->slug,
            'excerpt' => $p->excerpt, 'category' => $p->category,
            'category_label' => $p->category_label,
            'author_name' => $p->author_name,
            'published_at' => $p->published_at?->format('d/m/Y'),
            'cover_image' => $p->cover_image,
        ]);
        $categories = BlogPost::published()->distinct()->pluck('category');
        return Inertia::render('Public/Blog/Index', [
            'posts' => $posts,
            'categories' => $categories,
            'canLogin' => \Route::has('login'),
            'canRegister' => \Route::has('register'),
        ]);
    }

    public function show(string $slug): Response {
        $post = BlogPost::published()->where('slug', $slug)->firstOrFail();
        $related = BlogPost::published()
            ->where('category', $post->category)
            ->where('id', '!=', $post->id)
            ->latest('published_at')->take(3)->get()
            ->map(fn($p) => ['id' => $p->id, 'title' => $p->title, 'slug' => $p->slug, 'excerpt' => $p->excerpt, 'published_at' => $p->published_at?->format('d/m/Y')]);
        return Inertia::render('Public/Blog/Show', [
            'post' => [
                'id' => $post->id, 'title' => $post->title, 'excerpt' => $post->excerpt,
                'content' => $post->content, 'category' => $post->category,
                'category_label' => $post->category_label, 'author_name' => $post->author_name,
                'published_at' => $post->published_at?->format('d F Y'),
                'cover_image' => $post->cover_image,
                'meta_title' => $post->meta_title ?: $post->title,
                'meta_description' => $post->meta_description ?: $post->excerpt,
            ],
            'related' => $related,
            'canLogin' => \Route::has('login'),
            'canRegister' => \Route::has('register'),
        ]);
    }
}
