<section class="safari-section" id="stays">
    <h2>Accommodation &amp; meals</h2>
    @php($stayDays = $tour->accommodations->groupBy('pivot.day_number'))
    <table class="detail-stay-table">
        <thead><tr><th scope="col">Day</th><th scope="col">Accommodation</th><th scope="col">Meals &amp; drinks</th></tr></thead>
        <tbody>
        @for($dayNumber = 1; $dayNumber <= $tour->duration_days; $dayNumber++)
            <tr>
                <th scope="row">{{ $dayNumber }}</th>
                <td>
                    @forelse($stayDays->get($dayNumber, []) as $stay)
                        <div class="detail-stay-entry"><strong>{{ $stay->name }}</strong>
                            @if($stay->location || $stay->destination?->name)<p>{{ $stay->location ?: $stay->destination?->name }}</p>@endif
                            @include('public.stay-photo-strip')
                        </div>
                    @empty
                        <span>—</span>
                    @endforelse
                </td>
                <td>{{ ($itineraryDays[$dayNumber - 1]['meals'] ?? '') ?: '—' }}</td>
            </tr>
        @endfor
        </tbody>
    </table>
</section>
