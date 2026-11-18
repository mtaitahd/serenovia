<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\View\View;

class TravelPageController extends Controller
{
    public function about(): View
    {
        return view('travel.page', [
            'title' => 'About Serenovia Africa Safaris',
            'intro' => 'We help travellers plan journeys through Africa around the places, people and experiences they want to discover.',
            'sections' => [
                'How we plan a safari' => 'Tell us where you would like to go, how much time you have and the way you like to travel. Our team can help you compare itineraries and discuss stays, transport and guiding before you book.',
                'Talk to our team' => 'Ask us about a destination, a safari package or a journey you would like to shape from scratch.',
            ],
        ]);
    }

    public function responsible(): View
    {
        return view('travel.page', [
            'title' => 'Responsible travel',
            'intro' => 'A guide to travelling with care for the people, wildlife and landscapes you encounter.',
            'sections' => [
                'Respect local communities' => 'Ask before photographing people, follow the guidance of your host and give communities a say in how visits take place.',
                'Give wildlife space' => 'Follow your guide’s instructions, keep a respectful distance, avoid feeding animals and keep noise low during sightings.',
                'Leave less behind' => 'Carry a reusable bottle where safe refills are available and take your litter with you between stops.',
            ],
        ]);
    }

    public function guide(): View
    {
        return view('travel.page', [
            'title' => 'Travel guide',
            'intro' => 'Practical questions to consider as you plan your safari.',
            'sections' => [],
            'faqs' => Faq::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}
