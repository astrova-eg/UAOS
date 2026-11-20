<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Parsing;

use DOMDocument;
use DOMNode;
use DOMXPath;
use UniversalAgentOS\Exceptions\DocumentParsingException;
use UniversalAgentOS\Research\Contracts\DocumentParserInterface;
use UniversalAgentOS\Research\Sanitization\ContentSanitizer;
use UniversalAgentOS\Research\Sanitization\EvidenceSegmenter;

/**
 * Deterministic HTML parser extracting clean structural text, headings, and code blocks.
 */
class HtmlDocumentParser implements DocumentParserInterface
{
    private const SUPPORTED = ['text/html', 'application/xhtml+xml', 'html', 'htm'];

    public function __construct(
        private ?ContentSanitizer $sanitizer = null,
        private ?EvidenceSegmenter $segmenter = null
    ) {
        $this->sanitizer = $sanitizer ?? new ContentSanitizer();
        $this->segmenter = $segmenter ?? new EvidenceSegmenter();
    }

    public function supportsFormat(string $mimeOrExtension): bool
    {
        $normalized = strtolower(trim(explode(';', $mimeOrExtension)[0]));
        return in_array($normalized, self::SUPPORTED, true);
    }

    public function parse(array|string $document): array
    {
        $content = is_array($document) ? (string)($document['content'] ?? '') : $document;
        if (trim($content) === '') {
            throw new DocumentParsingException("HTML document content cannot be empty.");
        }

        $sanitizedResult = $this->sanitizer->sanitize($content);
        $cleanHtml = $sanitizedResult['sanitized'];

        $prevLibxml = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        // Load with safe libxml options (disable network/entity resolution)
        $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $cleanHtml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($prevLibxml);

        if (!$loaded) {
            throw new DocumentParsingException("Failed to parse HTML document into DOM structure.");
        }

        $title = '';
        $titleNodes = $dom->getElementsByTagName('title');
        if ($titleNodes->length > 0) {
            $title = trim($titleNodes->item(0)->textContent);
        }

        $xpath = new DOMXPath($dom);
        // Remove non-content elements
        $removeQuery = '//script|//style|//nav|//footer|//header|//aside|//noscript|//iframe|//svg';
        $toRemove = $xpath->query($removeQuery);
        if ($toRemove !== false) {
            foreach ($toRemove as $node) {
                if ($node->parentNode !== null) {
                    $node->parentNode->removeChild($node);
                }
            }
        }

        $body = $dom->getElementsByTagName('body')->item(0) ?? $dom->documentElement;
        $extractedText = $this->extractNodeText($body);
        $segments = $this->segmenter->segment($extractedText);

        return [
            'format' => 'html',
            'title' => $title,
            'text' => $extractedText,
            'segments' => $segments,
            'metadata' => [
                'redactions' => $sanitizedResult['redactions'],
                'detected_adversarial_patterns' => $sanitizedResult['detected_patterns'],
            ],
            'content_hash' => hash('sha256', $extractedText),
        ];
    }

    private function extractNodeText(?DOMNode $node): string
    {
        if ($node === null) {
            return '';
        }
        $text = '';
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text .= $child->textContent;
            } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                $childName = strtolower($child->nodeName);
                $childText = $this->extractNodeText($child);
                if (in_array($childName, ['p', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li', 'tr', 'blockquote'], true)) {
                    $text .= "\n\n" . trim($childText) . "\n\n";
                } elseif ($childName === 'br') {
                    $text .= "\n";
                } elseif ($childName === 'pre' || $childName === 'code') {
                    $text .= "\n```\n" . trim($childText) . "\n```\n";
                } else {
                    $text .= ' ' . $childText . ' ';
                }
            }
        }
        return preg_replace('/[ \t]+/', ' ', (string)preg_replace('/(\r?\n){3,}/', "\n\n", trim($text))) ?? trim($text);
    }
}
