<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\TourPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function create(Request $request): View
    {
        $tours = TourPackage::where('status', 'published')->orderBy('title')->get(['id', 'title']);

        $date = $request->query('travel_date');
        $party = $request->integer('travellers');

        return view('contact', [
            'tours' => $tours,
            'selectedTour' => $request->query('tour'),
            'preferredDate' => is_string($date) && validator(['date' => $date], ['date' => 'date_format:Y-m-d|after_or_equal:today'])->passes() ? $date : null,
            'preferredParty' => $party >= 1 && $party <= 100 ? $party : 2,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'tour_package_id' => ['nullable', Rule::exists('tour_packages', 'id')->where('status', 'published')],
            'travel_date' => ['nullable', 'date', 'after_or_equal:today'],
            'travellers' => ['required', 'integer', 'min:1', 'max:100'],
            'message' => ['nullable', 'string', 'max:4000'],
            'website' => ['nullable', 'max:0'],
        ]);
        unset($data['website']);
        Inquiry::create($data);

        return redirect()->route('contact')->with('success', 'Thank you. Your trip enquiry has been received. Our local team will be in touch.');
    }
}
