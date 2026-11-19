<details class="panel" style="margin-top:1rem">
    <summary><strong>Search visibility preview</strong></summary>
    <p>This preview uses saved content. Save a draft to refresh it. Search engines may choose different titles or snippets.</p>
    <p><strong>{{ $tour->title ?: 'Your tour title' }}</strong><br><small>{{ \App\Support\SeoMetadata::url('safaris/'.($tour->slug ?: 'your-tour-url')) }}</small></p>
    <p>{{ $tour->searchDescription() ?: 'Add an introduction or overview for the automatic search description.' }}</p>
    @if($tour->image_path)<img src="{{ asset($tour->image_path) }}" alt="Selected tour sharing image" width="240" style="max-width:100%;height:auto" loading="lazy">@endif
    @if($warnings = \App\Support\SeoReadiness::warnings($tour))<ul>@foreach($warnings as $warning)<li>{{ $warning }}</li>@endforeach</ul>@endif
    <small>Published URLs stay unchanged to protect shared links. Drafts are private. Published tours enter the sitemap automatically; price on request is supported.</small>
</details>
