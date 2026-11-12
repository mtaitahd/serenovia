@extends('admin.layout')
@section('title', 'Itinerary days')
@section('content')
@php($days = old('package_days', $packageDays->all()))
<div class="page-heading"><div><span class="eyebrow">SAFARI PACKAGE OPTIONS</span><h1>Itinerary days</h1><p>Choose which day numbers admins can select when assigning accommodation to a package.</p></div></div>
<form class="admin-form package-days-manager" method="POST" action="{{ route('admin.package-days.update', request()->boolean('popup') ? ['popup' => 1] : []) }}">
    @csrf @method('PUT')
    <section class="website-points-card">
        <h2>Available day options</h2>
        <p>Package forms show these as “Day 1”, “Day 2”, and so on. Only options up to a package’s duration are shown.</p>
        <div class="package-days-list" id="package-days-list">
            @foreach($days as $day)
                <div class="package-day-row"><span class="package-day-label">DAY</span><input name="package_days[]" type="number" min="1" max="365" value="{{ $day }}" aria-label="Package day number" required><button type="button" class="button button-light" data-remove-package-day aria-label="Remove day option">×</button></div>
            @endforeach
        </div>
        @error('package_days.*')<small class="field-error">{{ $message }}</small>@enderror
        <button type="button" class="button button-light" id="add-package-day">＋ Add day option</button>
        <div class="form-hint">Removing an option affects new package selections. Saved accommodation assignments remain unchanged.</div>
        <div class="form-actions"><button class="button" type="submit">Save day options <span>↗</span></button></div>
    </section>
</form>
<script>
(() => {
    const list = document.getElementById('package-days-list');
    document.getElementById('add-package-day')?.addEventListener('click', () => {
        const values = [...list.querySelectorAll('input')].map(input => Number(input.value)).filter(Number.isFinite);
        const next = Math.min(365, Math.max(0, ...values) + 1);
        const row = document.createElement('div');
        row.className = 'package-day-row';
        row.innerHTML = `<span class="package-day-label">DAY</span><input name="package_days[]" type="number" min="1" max="365" value="${next}" aria-label="Package day number" required><button type="button" class="button button-light" data-remove-package-day aria-label="Remove day option">×</button>`;
        list.append(row);
        row.querySelector('input').focus();
    });
    list?.addEventListener('click', event => {
        const button = event.target.closest('[data-remove-package-day]');
        if (button) button.closest('.package-day-row').remove();
    });
})();
</script>
@endsection
