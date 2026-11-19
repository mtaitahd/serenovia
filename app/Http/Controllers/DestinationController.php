<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\View\View;

class DestinationController extends Controller
{
    public function index(): View
    {
        return view('destinations.index', [
            'destinations' => Destination::withCount(['tours' => fn ($query) => $query->where('status', 'published')])
                ->where('status', 'published')->orderByDesc('is_featured')->orderBy('sort_order')->paginate(12),
        ]);
    }

    public function show(Destination $destination): View
    {
        abort_unless($destination->status === 'published', 404);

        return view('destinations.show', [
            'destination' => $destination,
            'tours' => $destination->tours()->with('destination')->publiclyVisible()->latest('published_at')->paginate(6),
        ]);
    }
}
