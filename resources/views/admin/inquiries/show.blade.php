@extends('admin.layout')
@section('title', 'Enquiry from '.$inquiry->name)
@section('content')
    <div class="page-heading"><div><span class="eyebrow">TRIP ENQUIRY · {{ $inquiry->created_at->format('M j, Y') }}</span><h1>{{ $inquiry->name }}</h1><p>Review the traveller's request and update its follow-up status.</p></div><a class="button secondary" href="{{ route('admin.inquiries.index') }}">← All enquiries</a></div>
    <section class="panel">
        <div class="inquiry-details">
            <div class="inquiry-fact"><span>Email</span><strong><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></strong></div>
            <div class="inquiry-fact"><span>Phone</span><strong>{{ $inquiry->phone ?: 'Not provided' }}</strong></div>
            <div class="inquiry-fact"><span>Interested in</span><strong>{{ $inquiry->tour?->title ?? 'A custom Tanzania journey' }}</strong></div>
            <div class="inquiry-fact"><span>Travel details</span><strong>{{ $inquiry->travel_date?->format('M j, Y') ?? 'Date flexible' }} · {{ $inquiry->travellers }} {{ \Illuminate\Support\Str::plural('traveller', $inquiry->travellers) }}</strong></div>
        </div>
        <h2 class="form-section-title">Message</h2><div class="inquiry-message">{{ $inquiry->message ?: 'No additional message was included.' }}</div>
        <form class="status-update" method="POST" action="{{ route('admin.inquiries.update', $inquiry) }}">@csrf @method('PATCH')
            <div class="field"><label for="status">FOLLOW-UP STATUS</label><select id="status" name="status">@foreach(['new','contacted','quoted','confirmed','closed'] as $status)<option value="{{ $status }}" @selected($inquiry->status === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <button class="button" type="submit">Update status ↗</button>
        </form>
        <form class="delete-inquiry" method="POST" action="{{ route('admin.inquiries.destroy', $inquiry) }}" onsubmit="return confirm('Delete this enquiry?')">@csrf @method('DELETE')<button class="text-button">Delete enquiry</button></form>
    </section>
@endsection
