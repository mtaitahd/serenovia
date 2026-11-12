<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\View\View;

class JournalController extends Controller
{
    public function index(): View
    {
        return view('journal.index', [
            'articles' => Article::where('status', 'published')->whereNotNull('published_at')->latest('published_at')->paginate(9),
        ]);
    }

    public function show(Article $article): View
    {
        abort_unless($article->status === 'published' && $article->published_at, 404);

        return view('journal.show', compact('article'));
    }
}
