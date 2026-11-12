<?php

namespace App\Support;

use DOMDocument;

class Itinerary
{
    /** Present existing editor content as days without changing the saved itinerary. */
    public static function sections(?string $value): array
    {
        $html = RichText::display($value);
        if ($html === '') return [];

        // Plain-text day entries may share one paragraph, separated by line breaks.
        $html = preg_replace('/<br\s*\/?>(\s*(?:Day|Days)\s+\d)/i', '</p><p>$1', $html);
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div id="itinerary-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $root = $document->getElementById('itinerary-root');
        if (! $root) return [];

        $sections = [];
        foreach ($root->childNodes as $node) {
            $text = trim($node->textContent);
            if (preg_match('/^(Days?\s+\d+(?:\s*[–—-]\s*\d+)?)\s*[:.\-–—]?\s*(.*)$/isu', $text, $matches)) {
                $isHeading = preg_match('/^h[1-6]$/', $node->nodeName)
                    || ($node->firstChild && in_array($node->firstChild->nodeName, ['strong', 'b']) && trim($node->firstChild->textContent) === $text);
                $sections[] = [
                    'label' => $matches[1],
                    'title' => $isHeading ? $matches[2] : '',
                    'html' => $isHeading ? '' : $document->saveHTML($node),
                ];
            } elseif ($text !== '' || $node->nodeName === 'img' || ($node instanceof \DOMElement && $node->getElementsByTagName('img')->length > 0)) {
                if (! $sections) $sections[] = ['label' => 'Your journey', 'title' => '', 'html' => ''];
                $sections[array_key_last($sections)]['html'] .= $document->saveHTML($node);
            }
        }

        return $sections;
    }
}
