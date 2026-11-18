                    <article class="tour-tile">
                        <a class="tour-tile-photo" href="{{ route('tours.show', $tour->slug) }}">
                            <img src="{{ asset(\App\Support\SafariPhotography::tour($tour)) }}" alt="" loading="lazy">
                            <span>{{ strtoupper($tour->tour_type) }}</span>
                            <h2>{{ $tour->title }}</h2>
                        </a>
                        <div class="tour-tile-content">
                            <p class="catalogue-card-price">{{ $tour->price_from !== null ? 'From '.$tour->currency.' '.number_format((float)$tour->price_from, 2) : 'Price on request' }}</p>
                            <p>@if($tour->destination?->country)<strong>{{ $tour->destination->country }}:</strong> @endif
                                {{ collect([ucfirst($tour->tour_type), $tour->max_group_size ? 'Up to '.$tour->max_group_size.' guests' : null, $tour->practical_details['stay_style'] ?? null])->filter()->implode(' · ') }}
                            </p>
                            @if($tour->starting_point || $tour->destination || $tour->ending_point)
                                <p><strong>You visit:</strong> {{ collect([$tour->starting_point ? $tour->starting_point.' (Start)' : null, $tour->destination?->name, $tour->ending_point ? $tour->ending_point.' (End)' : null])->filter()->implode(', ') }}</p>
                            @endif
                        </div>
                        <div class="catalogue-card-footer">
                            <img src="{{ asset('images/serenovia-logo.png') }}" alt="" width="42" height="38" loading="lazy">
                            <div class="catalogue-card-operator">
                                <strong>{{ $siteSettings['brand_name'] }}</strong>
                                <details class="catalogue-card-details">
                                    <summary>Trip details</summary>
                                    @if($tour->summary)<p>{{ $tour->summary }}</p>@endif
                                    <p>{{ $tour->duration_days }} days · {{ $tour->duration_nights }} nights</p>
                                    @if($tour->categories->isNotEmpty())<p>{{ $tour->categories->pluck('name')->implode(' · ') }}</p>@endif
                                    <a href="{{ route('tours.show', $tour->slug) }}">View itinerary ↗</a>
                                </details>
                            </div>
                        </div>
                    </article>
