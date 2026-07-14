<?php

declare(strict_types=1);

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

class ActivityPubContentSanitizer
{
    private const array ALLOWED_ELEMENTS = [
        'p', 'br', 'span', 'a', 'del', 'pre', 'code', 'em', 'strong',
        'b', 'i', 'u', 's', 'ul', 'ol', 'li', 'blockquote',
    ];

    private const array ELEMENT_ATTRIBUTES = [
        'a' => ['href', 'rel', 'class'],
        'span' => ['class'],
        'ol' => ['start', 'reversed'],
        'li' => ['value'],
    ];

    private const array ALLOWED_PROTOCOLS = [
        'http', 'https', 'dat', 'dweb', 'ipfs', 'ipns', 'ssb',
        'gopher', 'xmpp', 'magnet', 'gemini',
    ];

    private const array ALLOWED_CLASSES = ['mention', 'hashtag', 'ellipsis', 'invisible'];

    private const array ALLOWED_CLASS_PREFIXES = ['h-', 'p-', 'u-', 'dt-', 'e-'];

    public function sanitize(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return $html;
        }

        $doc = new DOMDocument;
        @$doc->loadHTML('<html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>');

        $body = $doc->getElementsByTagName('body')->item(0);
        if ($body === null) {
            return '';
        }

        return $this->sanitizeChildren($body);
    }

    private function sanitizeNode(DOMNode $node): string
    {
        if ($node instanceof DOMText) {
            return htmlspecialchars($node->nodeValue ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if (! $node instanceof DOMElement) {
            return '';
        }

        $tag = strtolower($node->tagName);

        if (! in_array($tag, self::ALLOWED_ELEMENTS, true)) {
            return $this->sanitizeChildren($node);
        }

        $inner = $this->sanitizeChildren($node);
        $attrs = $this->buildAttributes($tag, $node);

        if ($tag === 'br') {
            return '<br>';
        }

        return "<{$tag}{$attrs}>{$inner}</{$tag}>";
    }

    private function sanitizeChildren(DOMNode $node): string
    {
        $result = '';
        foreach ($node->childNodes as $child) {
            $result .= $this->sanitizeNode($child);
        }

        return $result;
    }

    private function buildAttributes(string $tag, DOMElement $node): string
    {
        $allowed = self::ELEMENT_ATTRIBUTES[$tag] ?? [];
        if ($allowed === []) {
            return '';
        }

        $attrs = '';
        foreach ($allowed as $attr) {
            if (! $node->hasAttribute($attr)) {
                continue;
            }

            $value = $node->getAttribute($attr);

            if ($attr === 'href' && ! $this->isAllowedProtocol($value)) {
                continue;
            }

            if ($attr === 'class') {
                $value = $this->filterClasses($value);
                if ($value === '') {
                    continue;
                }
            }

            $attrs .= ' '.$attr.'="'.htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8').'"';
        }

        return $attrs;
    }

    private function isAllowedProtocol(string $href): bool
    {
        $scheme = strtolower((string) parse_url($href, PHP_URL_SCHEME));

        return in_array($scheme, self::ALLOWED_PROTOCOLS, true);
    }

    private function filterClasses(string $classAttr): string
    {
        $classes = preg_split('/\s+/', trim($classAttr), -1, PREG_SPLIT_NO_EMPTY);
        if ($classes === false) {
            return '';
        }

        $allowed = array_filter($classes, fn (string $class) => $this->isAllowedClass($class));

        return implode(' ', $allowed);
    }

    private function isAllowedClass(string $class): bool
    {
        if (in_array($class, self::ALLOWED_CLASSES, true)) {
            return true;
        }

        return array_any(self::ALLOWED_CLASS_PREFIXES, fn ($prefix) => str_starts_with($class, $prefix));

    }
}
