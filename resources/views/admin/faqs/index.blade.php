@extends('admin.layout')
@section('title', 'FAQs')
@section('content')
    <div class="page-heading"><div><span class="eyebrow">HELPFUL DETAILS</span><h1>Frequently asked questions</h1><p>Keep visitor answers clear and up to date.</p></div><a class="button" href="{{ route('admin.faqs.create') }}">＋ Add FAQ</a></div>
    <section class="panel">
        @if($faqs->isEmpty())<div class="empty-state"><strong>No questions yet.</strong>Add an FAQ to help travellers plan with confidence.</div>
        @else
        <table class="data-table"><thead><tr><th>QUESTION</th><th>ORDER</th><th>STATUS</th><th>ACTIONS</th></tr></thead><tbody>
            @foreach($faqs as $faq)<tr><td><span class="table-title">{{ $faq->question }}</span><span class="table-sub">{{ \Illuminate\Support\Str::limit($faq->answer, 110) }}</span></td><td>{{ $faq->sort_order }}</td><td><span class="status-pill {{ $faq->is_active ? 'published' : 'draft' }}">{{ $faq->is_active ? 'Visible' : 'Hidden' }}</span></td><td><div class="row-actions"><a class="text-button" href="{{ route('admin.faqs.edit', $faq) }}">Edit</a><form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" onsubmit="return confirm('Delete this FAQ?')">@csrf @method('DELETE')<button class="text-button">Delete</button></form></div></td></tr>@endforeach
        </tbody></table><div class="pagination-wrap">@include('admin.partials.pagination', ['paginator' => $faqs])</div>
        @endif
    </section>
@endsection
