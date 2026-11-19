@extends('admin.layout')
@section('title', $faq->exists ? 'Edit FAQ' : 'New FAQ')
@section('content')
    <div class="page-heading"><div><span class="eyebrow">HELPFUL DETAILS</span><h1>{{ $faq->exists ? 'Edit a question' : 'Add a question' }}</h1><p>Keep answers straightforward and relevant to your guests.</p></div><a class="button secondary" href="{{ route('admin.faqs.index') }}">← All FAQs</a></div>
    <form class="admin-form" method="POST" action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}">
        @csrf @if($faq->exists) @method('PUT') @endif
        <div class="form-grid">
            <div class="field full"><label for="question">QUESTION *</label><input id="question" name="question" value="{{ old('question', $faq->question) }}" required></div>
            <div class="field full"><label for="answer">ANSWER *</label><textarea id="answer" name="answer" rows="8" required>{{ old('answer', $faq->answer) }}</textarea></div>
            <div class="field"><label for="sort_order">DISPLAY ORDER</label><input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $faq->sort_order ?? 0) }}"></div>
            <div class="field"><label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $faq->exists ? $faq->is_active : true))> Show this question on the website</label></div>
        </div>
        <div class="form-actions"><button class="button" type="submit">Save FAQ </button><a class="button secondary" href="{{ route('admin.faqs.index') }}">Cancel</a></div>
    </form>
@endsection
