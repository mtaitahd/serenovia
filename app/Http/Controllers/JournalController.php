<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\View\View;

class JournalController extends Controller
{
    public function index(): View
    {
        return view('journal.index', [
            'articles' => Article::publiclyVisible()->latest('published_at')->paginate(9),
        ]);
    }

    public function show(Article $article): View
    {
        abort_unless(Article::publiclyVisible()->whereKey($article->id)->exists(), 404);

        return view('journal.show', compact('article'));
    }
}
