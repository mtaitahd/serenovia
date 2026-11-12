<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Destination;
use App\Models\GalleryImage;
use App\Models\Inquiry;
use App\Models\TourPackage;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $firstMonth = CarbonImmutable::now()->startOfMonth()->subMonths(11);
        $months = collect(range(0, 11))->map(function (int $offset) use ($firstMonth) {
            $start = $firstMonth->addMonths($offset);
            return [
                'label' => $start->format('M y'),
                'total' => Inquiry::where('created_at', '>=', $start)->where('created_at', '<', $start->addMonth())->count(),
            ];
        });

        $destinationCounts = TourPackage::query()
            ->leftJoin('destinations', 'tour_packages.destination_id', '=', 'destinations.id')
            ->selectRaw("COALESCE(destinations.name, 'Unassigned') as label, COUNT(tour_packages.id) as total")
            ->groupBy('destinations.name')
            ->orderByDesc('total')
            ->get();
        $topDestinations = $destinationCounts->take(5);
        $otherTours = $destinationCounts->skip(5)->sum('total');
        if ($otherTours > 0) {
            $topDestinations->push((object) ['label' => 'Other', 'total' => $otherTours]);
        }

        return view('admin.dashboard', [
            'stats' => [
                'tours' => TourPackage::count(),
                'destinations' => Destination::count(),
                'articles' => Article::count(),
                'gallery' => GalleryImage::count(),
                'new_inquiries' => Inquiry::where('status', 'new')->count(),
            ],
            'inquiries' => Inquiry::with('tour')->latest()->take(6)->get(),
            'latest_tours' => TourPackage::latest()->take(5)->get(),
            'inquiryTrend' => $months,
            'toursByDestination' => $topDestinations->values(),
        ]);
    }
}
