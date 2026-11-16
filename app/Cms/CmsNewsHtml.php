<?php

namespace App\Cms;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use Illuminate\Support\HtmlString;

final class CmsNewsHtml
{
    /**
     * @var list<string>
     */
    private const ALLOWED = ['p', 'h2', 'h3', 'strong', 'em', 'ul', 'ol', 'li', 'a', 'br'];

    /**
     * @var array<string, string>
     */
    private const RENAME = [
        'b' => 'strong',
        'i' => 'em',
    ];

    /**
     * @var list<string>
     */
    private const DROP_WITH_CHILDREN = [
        'script', 'style', 'iframe', 'object', 'embed', 'applet',
        'form', 'input', 'button', 'textarea', 'select', 'option',
        'link', 'meta', 'svg', 'math', 'video', 'audio', 'source',
        'img', 'picture', 'canvas', 'template', 'noscript',
    ];

    public static function looksLikeHtml(string $value): bool
    {
        return (bool) preg_match('/<\/?[a-z][a-z0-9]*\b/i', $value);
    }

    public static function normalizeIncoming(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (! self::looksLikeHtml($value)) {
            return $value;
        }

        return self::sanitize($value);
    }

    public static function sanitize(string $html): string
    {
        $html = trim($html);

        if ($html === '') {
            return '';
        }

        $previous = libxml_use_internal_errors(true);

        $document = new DOMDocument('1.0', 'UTF-8');
        $wrapped = '<div id="cms-news-root">'.$html.'</div>';
        $payload = '<?xml encoding="UTF-8">'.$wrapped;
        $document->loadHTML($payload, LIBXML_NONET | LIBXML_NOWARNING | LIBXML_NOERROR);

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('cms-news-root');
        if (! $root instanceof DOMElement) {
            return '';
        }

        $cleaned = self::cleanChildren($root, $document);

        $output = '';
        foreach ($cleaned as $child) {
            $output .= $document->saveHTML($child);
        }

        return trim($output);
    }

    public static function toSafeHtml(string $body): string
    {
        $body = str_replace("\r\n", "\n", $body);

        if (! self::looksLikeHtml($body)) {
            return self::plainTextToHtml($body);
        }

        return self::sanitize($body);
    }

    public static function toSafeHtmlString(string $body): HtmlString
    {
        return new HtmlString(self::toSafeHtml($body));
    }

    public static function editorHtml(string $body): string
    {
        $body = trim(str_replace("\r\n", "\n", $body));

        if ($body === '') {
            return '<p></p>';
        }

        if (! self::looksLikeHtml($body)) {
            return self::plainTextToHtml($body);
        }

        $safe = self::sanitize($body);

        return $safe !== '' ? $safe : '<p></p>';
    }

    public static function plainTextToHtml(string $body): string
    {
        $body = trim(str_replace("\r\n", "\n", $body));

        if ($body === '') {
            return '';
        }

        $paragraphs = preg_split("/\n\n+/", $body) ?: [];
        $html = '';

        foreach ($paragraphs as $paragraph) {
            $escaped = htmlspecialchars($paragraph, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $escaped = nl2br($escaped, false);
            $html .= '<p>'.$escaped.'</p>';
        }

        return $html;
    }

    /**
     * @return list<DOMNode>
     */
    private static function cleanChildren(DOMNode $parent, DOMDocument $document): array
    {
        $nodes = [];

        foreach (iterator_to_array($parent->childNodes) as $child) {
            foreach (self::cleanNode($child, $document) as $kept) {
                $nodes[] = $kept;
            }
        }

        return $nodes;
    }

    /**
     * @return list<DOMNode>
     */
    private static function cleanNode(DOMNode $node, DOMDocument $document): array
    {
        if ($node instanceof DOMText) {
            return [$document->createTextNode($node->textContent)];
        }

        if (! $node instanceof DOMElement) {
            return [];
        }

        $tag = strtolower($node->tagName);

        if (in_array($tag, self::DROP_WITH_CHILDREN, true)) {
            return [];
        }

        if (isset(self::RENAME[$tag])) {
            $tag = self::RENAME[$tag];
        }

        if (! in_array($tag, self::ALLOWED, true)) {
            return self::cleanChildren($node, $document);
        }

        if ($tag === 'br') {
            return [$document->createElement('br')];
        }

        $element = $document->createElement($tag);

        if ($tag === 'a') {
            $href = self::safeHref($node->getAttribute('href'));
            if ($href === null) {
                return self::cleanChildren($node, $document);
            }
            $element->setAttribute('href', $href);
            $element->setAttribute('rel', 'noopener noreferrer');
        }

        foreach (self::cleanChildren($node, $document) as $child) {
            $element->appendChild($child);
        }

        if ($tag !== 'br' && $element->childNodes->length === 0 && trim($element->textContent) === '') {
            return [];
        }

        return [$element];
    }

    private static function safeHref(string $href): ?string
    {
        $href = trim(html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($href === '' || str_contains($href, "\0") || preg_match('/\s/', $href)) {
            return null;
        }

        if (preg_match('/^(javascript|data|vbscript|file|about):/i', $href)) {
            return null;
        }

        $parts = parse_url($href);
        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        $scheme = strtolower($parts['scheme']);
        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        if ($parts['host'] === '' || str_contains($parts['host'], '..')) {
            return null;
        }

        return $href;
    }
}
