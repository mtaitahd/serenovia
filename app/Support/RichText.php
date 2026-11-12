<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

class RichText
{
    private const ALLOWED_TAGS = [
        'a', 'b', 'blockquote', 'br', 'caption', 'code', 'div', 'em', 'h1', 'h2', 'h3',
        'h4', 'h5', 'h6', 'hr', 'i', 'img', 'li', 'ol', 'p', 'pre', 's', 'span',
        'strong', 'sub', 'sup', 'table', 'tbody', 'td', 'th', 'thead', 'tr', 'u', 'ul',
    ];

    private const DROP_TAGS = ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math'];

    public static function display(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') return '';

        if (! preg_match('/<\/?[a-z][^>]*>/i', $value)) {
            return collect(preg_split('/\r?\n\s*\r?\n/', $value) ?: [])
                ->map(fn ($paragraph) => '<p>'.nl2br(e(trim($paragraph))).'</p>')
                ->implode('');
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div id="rich-text-root">'.$value.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('rich-text-root');
        if (! $root) return e(strip_tags($value));

        self::cleanChildren($root);
        $html = '';
        foreach ($root->childNodes as $child) $html .= $document->saveHTML($child);

        return $html;
    }

    private static function cleanChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node instanceof DOMText) continue;
            if (! $node instanceof DOMElement) {
                $parent->removeChild($node);
                continue;
            }

            $tag = strtolower($node->tagName);
            if (in_array($tag, self::DROP_TAGS, true)) {
                $parent->removeChild($node);
                continue;
            }

            self::cleanChildren($node);
            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                while ($node->firstChild) $parent->insertBefore($node->firstChild, $node);
                $parent->removeChild($node);
                continue;
            }

            $allowedAttributes = match ($tag) {
                'a' => ['href', 'title', 'target', 'rel'],
                'img' => ['src', 'alt', 'title', 'width', 'height'],
                'td', 'th' => ['colspan', 'rowspan'],
                default => [],
            };

            foreach (iterator_to_array($node->attributes) as $attribute) {
                $name = strtolower($attribute->name);
                if ($name === 'style') {
                    $safeStyle = self::safeStyle($attribute->value);
                    if ($safeStyle !== '') $node->setAttribute('style', $safeStyle);
                    else $node->removeAttribute($name);
                    continue;
                }
                if (! in_array($name, $allowedAttributes, true)) $node->removeAttribute($name);
            }

            if ($tag === 'a') {
                $href = trim($node->getAttribute('href'));
                if ($href !== '' && ! preg_match('#^(https?://|mailto:|tel:|/|\#|\?)#i', $href)) {
                    $node->removeAttribute('href');
                }
                if ($node->getAttribute('target') === '_blank') $node->setAttribute('rel', 'noopener noreferrer');
                elseif ($node->hasAttribute('target')) $node->removeAttribute('target');
            }

            if ($tag === 'img') {
                $src = trim($node->getAttribute('src'));
                if (! preg_match('#^(https?://|/)#i', $src)) $node->parentNode?->removeChild($node);
            }
        }
    }

    private static function safeStyle(string $style): string
    {
        $safe = [];
        foreach (explode(';', $style) as $declaration) {
            if (! str_contains($declaration, ':')) continue;
            [$property, $value] = array_map('trim', explode(':', $declaration, 2));
            $property = strtolower($property);
            $value = strtolower($value);
            $valid = match ($property) {
                'color', 'background-color' => (bool) preg_match('/^(#[0-9a-f]{3,8}|[a-z]{3,20}|rgba?\([0-9.% ,]+\))$/i', $value),
                'text-align' => in_array($value, ['left', 'right', 'center', 'justify'], true),
                'font-size' => (bool) preg_match('/^(\d+(\.\d+)?(px|pt|em|rem|%))$/', $value),
                'font-family' => (bool) preg_match('/^[a-z0-9 ,"\'-]{1,100}$/i', $value),
                default => false,
            };
            if ($valid) $safe[] = $property.':'.$value;
        }

        return implode(';', $safe);
    }
}
