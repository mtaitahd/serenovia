@extends('admin.layout')
@section('title', 'Start & End Points')
@section('content')
<div class="page-heading"><div><span class="eyebrow">WEBSITE CONTENT / SAFARI DETAILS</span><h1>Start & End Points</h1><p>Maintain the lists used when setting a safari package’s route.</p></div></div>
@php
    $startingPoints = old('starting_points', preg_split('/\r?\n/', $startingPointsText, -1, PREG_SPLIT_NO_EMPTY));
    $endingPoints = old('ending_points', preg_split('/\r?\n/', $endingPointsText, -1, PREG_SPLIT_NO_EMPTY));
@endphp
<form class="admin-form" method="POST" action="{{ route('admin.website-content.route-points.update', request()->boolean('popup') ? ['popup' => 1] : []) }}">@csrf @method('PUT')
    <div class="website-points-grid">
        @foreach(['starting_points' => ['Starting points', $startingPoints], 'ending_points' => ['Ending points', $endingPoints]] as $field => [$heading, $points])
            <section class="website-points-card"><h2>{{ $heading }}</h2><p>{{ $field === 'starting_points' ? 'These locations populate the starting location options on safari packages.' : 'These locations populate the drop-off options on safari packages.' }}</p>
                <div class="website-points-list" data-points-list="{{ $field }}">
                    @foreach($points as $index => $point)
                        <div class="website-point-row"><input name="{{ $field }}[{{ $index }}]" value="{{ $point }}" maxlength="255" placeholder="e.g. Moshi, Tanzania"><button type="button" class="button button-light" data-remove-point aria-label="Remove point">×</button></div>
                    @endforeach
                </div>
                <button type="button" class="button button-light" data-add-point="{{ $field }}">+ Add {{ $field === 'starting_points' ? 'starting' : 'ending' }} point</button>
            </section>
        @endforeach
    </div>
    <script>
    (() => {
        document.querySelectorAll('[data-add-point]').forEach(button => button.addEventListener('click', () => {
            const field = button.dataset.addPoint;
            const list = document.querySelector(`[data-points-list="${field}"]`);
            const index = Number(list.dataset.nextIndex || list.querySelectorAll('.website-point-row').length);
            list.dataset.nextIndex = index + 1;
            const row = document.createElement('div');
            row.className = 'website-point-row';
            row.innerHTML = `<input name="${field}[${index}]" maxlength="255" placeholder="e.g. Tanzania"><button type="button" class="button button-light" data-remove-point aria-label="Remove point">×</button>`;
            list.appendChild(row);
        }));
        document.addEventListener('click', event => {
            const button = event.target.closest('[data-remove-point]');
            if (button) button.closest('.website-point-row').remove();
        });
        document.querySelectorAll('[data-points-list]').forEach(list => list.dataset.nextIndex = list.querySelectorAll('.website-point-row').length);
    })();
    </script>
    <div class="form-hint">Removing an option here changes choices for new packages; saved safari routes remain intact.</div>
    <div class="form-actions"><button class="button" type="submit">Save route points <span>↗</span></button></div>
</form>
@endsection
