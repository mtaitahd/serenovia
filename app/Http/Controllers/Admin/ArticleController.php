<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(): View
    {
        return view('admin.articles.index', ['articles' => Article::latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('admin.articles.form', ['article' => new Article()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $image = $data['image'] ?? null;
        unset($data['image']);
        $data['slug'] = Str::slug($data['slug'] ?: $data['title']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['published_at'] = $data['status'] === 'published' ? ($data['published_at'] ?? now()) : null;
        if ($image) $data['image_path'] = PublicImage::store($image, 'articles');
        Article::create($data);

        return redirect()->route('admin.articles.index')->with('success', 'Journal article created.');
    }

    public function edit(Article $article): View
    {
        return view('admin.articles.form', compact('article'));
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $data = $this->validated($request, $article);
        $image = $data['image'] ?? null;
        unset($data['image']);
        $data['slug'] = Str::slug($data['slug'] ?: $data['title']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['published_at'] = $data['status'] === 'published' ? ($data['published_at'] ?? $article->published_at ?? now()) : null;
        if ($image) {
            PublicImage::delete($article->image_path);
            $data['image_path'] = PublicImage::store($image, 'articles');
        }
        $article->update($data);

        return redirect()->route('admin.articles.index')->with('success', 'Journal article updated.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        PublicImage::delete($article->image_path);
        $article->delete();

        return back()->with('success', 'Journal article deleted.');
    }

    private function validated(Request $request, ?Article $article = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('articles', 'slug')->ignore($article?->id)],
            'category' => ['required', 'string', 'max:80'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:50000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
        ]);
    }
}
