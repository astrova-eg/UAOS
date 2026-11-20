<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Parsing;

use DOMDocument;
use UniversalAgentOS\Exceptions\DocumentParsingException;
use UniversalAgentOS\Research\Contracts\DocumentParserInterface;
use UniversalAgentOS\Research\Sanitization\ContentSanitizer;
use UniversalAgentOS\Research\Sanitization\EvidenceSegmenter;

/**
 * Hardened XML/RSS/Atom parser with strict XXE protection and entity expansion prevention.
 */
class XmlDocumentParser implements DocumentParserInterface
{
    private const SUPPORTED = [
        'application/xml', 'text/xml', 'xml',
        'application/rss+xml', 'application/atom+xml', 'rss', 'atom'
    ];

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
            throw new DocumentParsingException("XML content cannot be empty.");
        }

        // XXE Pre-check: reject DTD / external entity declarations
        if (preg_match('/<!DOCTYPE|<!ENTITY/i', $content)) {
            throw new DocumentParsingException("XXE protection blocked: DOCTYPE and ENTITY declarations are prohibited in XML.");
        }

        $prevLibxml = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        // Enforce strict LIBXML_NONET
        $loaded = $dom->loadXML($content, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($prevLibxml);

        if (!$loaded) {
            throw new DocumentParsingException("Malformed XML document.");
        }

        $title = '';
        $titleNodes = $dom->getElementsByTagName('title');
        if ($titleNodes->length > 0) {
            $title = trim($titleNodes->item(0)->textContent);
        }

        $lines = [];
        $this->extractTextFromXml($dom->documentElement, $lines);
        $rawText = implode("\n\n", $lines);

        $sanitizedResult = $this->sanitizer->sanitize($rawText);
        $cleanText = $sanitizedResult['sanitized'];

        $segments = $this->segmenter->segment($cleanText);

        return [
            'format' => 'xml',
            'title' => $title,
            'text' => $cleanText,
            'segments' => $segments,
            'metadata' => [
                'redactions' => $sanitizedResult['redactions'],
                'detected_adversarial_patterns' => $sanitizedResult['detected_patterns'],
            ],
            'content_hash' => hash('sha256', $cleanText),
        ];
    }

    /**
     * @param list<string> $lines
     */
    private function extractTextFromXml(?\DOMElement $element, array &$lines, int $depth = 0): void
    {
        if ($element === null || $depth > 12) {
            return;
        }

        $tagName = strtolower($element->nodeName);
        $hasChildElements = false;
        foreach ($element->childNodes as $child) {
            if ($child instanceof \DOMElement) {
                $hasChildElements = true;
                $this->extractTextFromXml($child, $lines, $depth + 1);
            }
        }

        if (!$hasChildElements) {
            $txt = trim($element->textContent);
            if ($txt !== '') {
                $lines[] = "{$tagName}: {$txt}";
            }
        }
    }
}
