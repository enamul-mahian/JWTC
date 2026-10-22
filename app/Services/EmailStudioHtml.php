<?php

namespace App\Services;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;

class EmailStudioHtml
{
    private const TAGS = [
        'p','div','span','br','strong','b','em','i','u','s','del','strike',
        'ul','ol','li','blockquote','h1','h2','h3','a','table','thead',
        'tbody','tr','td','th','hr',
    ];

    private const REMOVE_CONTENT = [
        'script','style','iframe','object','embed','svg','math','form','input',
        'select','textarea','button','video','audio','source','picture','img',
        'link','meta','base','template','noscript',
    ];

    public function sanitize(string $html): string
    {
        if (!extension_loaded('dom')) {
            throw new \RuntimeException('DOM extension is required for safe email editing.');
        }

        $previous = libxml_use_internal_errors(true);
        try {
            $doc = new DOMDocument('1.0', 'UTF-8');
            $doc->loadHTML(
                '<?xml encoding="UTF-8"><!doctype html><html><body>' .
                '<div id="jwtc-safe-html-root">' . $html . '</div></body></html>',
                LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
            );

            $root = $doc->getElementById('jwtc-safe-html-root');
            if (!$root) {
                return '';
            }

            foreach (iterator_to_array($root->childNodes) as $child) {
                $this->scrub($child);
            }

            $output = '';
            foreach ($root->childNodes as $child) {
                $output .= $doc->saveHTML($child);
            }

            return trim($output);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    public function personalize(string $text, array $values, bool $html = true): string
    {
        return preg_replace_callback('/\{\{\s*(client_name|reference|status|message|date)\s*\}\}/i',
            static function ($match) use ($values, $html) {
                $value = (string) ($values[strtolower($match[1])] ?? '');
                return $html ? htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : $value;
            }, $text
        );
    }

    public function plainText(string $html): string
    {
        $html = preg_replace('/<(?:br|\/p|\/div|\/li|\/tr|\/h[1-3])\b[^>]*>/i', "\n", $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[ \t]+\n/", "\n", $text);
        return trim(preg_replace("/\n{3,}/", "\n\n", $text));
    }

    private function scrub(DOMNode $node): void
    {
        if ($node instanceof DOMComment) {
            $node->parentNode?->removeChild($node);
            return;
        }
        if (!$node instanceof DOMElement) {
            if ($node->nodeType !== XML_TEXT_NODE) {
                $node->parentNode?->removeChild($node);
            }
            return;
        }

        $tag = strtolower($node->tagName);
        if (in_array($tag, self::REMOVE_CONTENT, true)) {
            $node->parentNode?->removeChild($node);
            return;
        }

        foreach (iterator_to_array($node->childNodes) as $child) {
            $this->scrub($child);
        }

        if (!in_array($tag, self::TAGS, true)) {
            while ($node->firstChild) {
                $node->parentNode->insertBefore($node->firstChild, $node);
            }
            $node->parentNode?->removeChild($node);
            return;
        }

        foreach (iterator_to_array($node->attributes) as $attribute) {
            $key = strtolower($attribute->name);
            $value = $attribute->value;

            if ($key === 'style') {
                $safe = $this->safeStyle($value);
                if ($safe === '') {
                    $node->removeAttribute($attribute->name);
                } else {
                    $node->setAttribute('style', $safe);
                }
                continue;
            }

            // Preserve safe legacy table layout attributes so imported .html
            // messages keep their basic Gmail-friendly structure.
            if (in_array($tag, ['table', 'td', 'th'], true) &&
                $key === 'width' && preg_match('/^(?:[1-9][0-9]{0,2}(?:px)?|100%|[1-9][0-9]?%)$/', $value)) {
                continue;
            }
            if (in_array($tag, ['table', 'td', 'th'], true) &&
                $key === 'align' && in_array(strtolower($value), ['left', 'center', 'right'], true)) {
                continue;
            }
            if (in_array($tag, ['td', 'th'], true) && $key === 'valign' &&
                in_array(strtolower($value), ['top', 'middle', 'bottom'], true)) {
                continue;
            }
            if (in_array($tag, ['td', 'th'], true) && in_array($key, ['colspan', 'rowspan'], true)
                && preg_match('/^[1-4]$/', $value)) {
                continue;
            }
            if ($tag === 'table' && in_array($key, ['cellpadding', 'cellspacing'], true)
                && preg_match('/^(?:[0-9]|1[0-9]|20)$/', $value)) {
                continue;
            }
            if ($tag === 'table' && $key === 'border' && $value === '0') {
                continue;
            }

            if ($key === 'href' && $tag === 'a' && $this->safeHref($value)) {
                $node->setAttribute('rel', 'noopener noreferrer');
                continue;
            }

            if ($key !== 'rel') {
                $node->removeAttribute($attribute->name);
            }
        }

        if ($tag === 'a') {
            $node->removeAttribute('target');
            if (!$node->hasAttribute('href')) {
                $node->removeAttribute('rel');
            }
        }
    }

    private function safeHref(string $value): bool
    {
        if (preg_match('/[\x00-\x1F\x7F]/', $value) || strlen($value) > 1200) {
            return false;
        }
        $scheme = strtolower((string) parse_url(trim($value), PHP_URL_SCHEME));
        if (!in_array($scheme, ['https', 'http', 'mailto'], true)) {
            return false;
        }
        if ($scheme === 'mailto') {
            return filter_var(substr($value, 7), FILTER_VALIDATE_EMAIL) !== false;
        }
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    private function safeStyle(string $value): string
    {
        $style = [];
        foreach (explode(';', $value) as $part) {
            $pair = explode(':', $part, 2);
            if (count($pair) !== 2) {
                continue;
            }
            [$key, $val] = [strtolower(trim($pair[0])), trim($pair[1])];
            if (strlen($val) > 100 || preg_match('/[<>\\{}\x00-\x1F]|url\s*\(|expression\s*\(|@/i', $val)) {
                continue;
            }
            $valid = match ($key) {
                'text-align' => preg_match('/^(left|right|center|justify)$/i', $val),
                'color','background-color' => preg_match('/^(#[0-9a-f]{3}(?:[0-9a-f]{3})?|rgb\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*\))$/i', $val),
                'font-size' => preg_match('/^(1[0-9]|2[0-8])px$/', $val),
                'font-family' => preg_match('/^(Arial|Georgia|Verdana|Tahoma|Helvetica|Times New Roman)(?:,\s*(sans-serif|serif))?$/i', $val),
                'font-weight' => preg_match('/^(normal|bold|[4-7]00)$/i', $val),
                'font-style' => preg_match('/^(normal|italic)$/i', $val),
                'text-decoration' => preg_match('/^(none|underline|line-through)$/i', $val),
                'line-height' => preg_match('/^(1(\.[0-9])?|2(\.0)?)$/', $val),
                'width','max-width' => preg_match('/^(?:[1-9][0-9]{0,2}px|100%|[1-9][0-9]?%)$/', $val),
                'padding' => preg_match('/^(?:[0-9]|[1-4][0-9]|50)px(?:\\s+(?:[0-9]|[1-4][0-9]|50)px){0,3}$/', $val),
                'border-collapse' => $val === 'collapse',
                'vertical-align' => in_array($val, ['top','middle','bottom'], true),
                default => false,
            };
            if ($valid) {
                $style[$key] = $key . ':' . $val;
            }
        }

        return implode(';', array_values($style));
    }
}
