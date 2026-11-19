<?php

namespace App\Support;

final class StructuredData
{
    public static function graph(array $data, $settings, array $seo, string $title): array
    {
        $home = SeoMetadata::url();
        $orgId = $home.'#organization';
        $organization = ['@type' => 'TravelAgency', '@id' => $orgId, 'name' => $settings['brand_name'] ?? 'Serenovia Africa Safaris', 'url' => $home];
        foreach (['phone' => 'telephone', 'email' => 'email', 'address' => 'address'] as $key => $property) {
            if (!empty($settings[$key])) $organization[$property] = $settings[$key];
        }
        $profiles = [];
        foreach (['instagram', 'facebook', 'youtube', 'linkedin'] as $key) {
            if (filter_var($settings[$key] ?? '', FILTER_VALIDATE_URL)) $profiles[] = $settings[$key];
        }
        if ($profiles) $organization['sameAs'] = $profiles;
        $organization['logo'] = SeoMetadata::url($settings['site_logo'] ?? 'images/serenovia-logo.png');
        $page = ['@type' => 'WebPage', '@id' => $seo['canonical'].'#webpage', 'url' => $seo['canonical'], 'name' => $title, 'description' => $seo['description'], 'inLanguage' => 'en', 'isPartOf' => ['@id' => $home.'#website']];
        $graph = [$organization, ['@type' => 'WebSite', '@id' => $home.'#website', 'url' => $home, 'name' => $organization['name'], 'publisher' => ['@id' => $orgId]], $page];
        $crumbs = [['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $home]];
        $entity = null;
        if (isset($data['tour'])) {
            $tour = $data['tour'];
            $entity = ['@type' => 'TouristTrip', 'name' => $tour->title, 'description' => SeoMetadata::text($tour->description ?: $tour->summary, 5000), 'provider' => ['@id' => $orgId]];
            $days = $data['itineraryDays'] ?? SafariDays::sections($tour);
            $items = [];
            foreach ($days as $index => $day) {
                $name = $day['location'] ?? null;
                if (is_array($name)) $name = null;
                if ($name) $items[] = ['@type' => 'ListItem', 'position' => count($items) + 1, 'item' => ['@type' => 'Place', 'name' => SeoMetadata::text($name, 300)]];
            }
            if ($items) $entity['itinerary'] = ['@type' => 'ItemList', 'itemListElement' => $items];
            // A headline "from" price is not a verified dated offer or availability inventory.
            $crumbs[] = ['@type' => 'ListItem', 'position' => 2, 'name' => 'Safaris', 'item' => SeoMetadata::url('safaris')];
        } elseif (isset($data['destination'])) {
            $entity = ['@type' => 'TouristDestination', 'name' => $data['destination']->name, 'description' => $seo['description']];
            $crumbs[] = ['@type' => 'ListItem', 'position' => 2, 'name' => 'Destinations', 'item' => SeoMetadata::url('destinations')];
        } elseif (isset($data['article'])) {
            $article = $data['article'];
            $entity = ['@type' => 'BlogPosting', 'headline' => $article->title, 'description' => $seo['description'], 'publisher' => ['@id' => $orgId]];
            if ($article->published_at) $entity['datePublished'] = $article->published_at->toAtomString();
            if ($article->updated_at) $entity['dateModified'] = $article->updated_at->toAtomString();
            $crumbs[] = ['@type' => 'ListItem', 'position' => 2, 'name' => 'Journal', 'item' => SeoMetadata::url('journal')];
        }
        if ($entity) {
            $entity += ['@id' => $seo['canonical'].'#entity', 'url' => $seo['canonical'], 'image' => $seo['image'], 'mainEntityOfPage' => ['@id' => $page['@id']]];
            $graph[2]['mainEntity'] = ['@id' => $entity['@id']];
            $graph[] = $entity;
            $crumbs[] = ['@type' => 'ListItem', 'position' => count($crumbs) + 1, 'name' => $entity['name'] ?? $entity['headline'], 'item' => $seo['canonical']];
            $graph[] = ['@type' => 'BreadcrumbList', '@id' => $seo['canonical'].'#breadcrumb', 'itemListElement' => $crumbs];
            $graph[2]['breadcrumb'] = ['@id' => $seo['canonical'].'#breadcrumb'];
        }
        return ['@context' => 'https://schema.org', '@graph' => $graph];
    }
}
