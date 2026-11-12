<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Inquiry::with('tour')->latest();
        if ($request->filled('status')) $query->where('status', $request->string('status'));

        return view('admin.inquiries.index', ['inquiries' => $query->paginate(20)->withQueryString()]);
    }

    public function show(Inquiry $inquiry): View
    {
        return view('admin.inquiries.show', ['inquiry' => $inquiry->load('tour')]);
    }

    public function update(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['new', 'contacted', 'quoted', 'confirmed', 'closed'])]]);
        $inquiry->update($data);

        return back()->with('success', 'Inquiry status updated.');
    }

    public function destroy(Inquiry $inquiry): RedirectResponse
    {
        $inquiry->delete();

        return redirect()->route('admin.inquiries.index')->with('success', 'Inquiry deleted.');
    }
}
