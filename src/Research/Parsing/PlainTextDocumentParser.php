<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Parsing;

use UniversalAgentOS\Exceptions\DocumentParsingException;
use UniversalAgentOS\Research\Contracts\DocumentParserInterface;
use UniversalAgentOS\Research\Sanitization\ContentSanitizer;
use UniversalAgentOS\Research\Sanitization\EvidenceSegmenter;

/**
 * Plain text document parser normalizing whitespace and generating atomic hashed segments.
 */
class PlainTextDocumentParser implements DocumentParserInterface
{
    private const SUPPORTED = ['text/plain', 'txt', 'log'];

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
            throw new DocumentParsingException("Plain text content cannot be empty.");
        }

        $sanitizedResult = $this->sanitizer->sanitize($content);
        $cleanText = $sanitizedResult['sanitized'];

        $normalized = preg_replace('/(\r?\n){3,}/', "\n\n", $cleanText) ?? $cleanText;
        $segments = $this->segmenter->segment($normalized);

        return [
            'format' => 'plain_text',
            'title' => '',
            'text' => $normalized,
            'segments' => $segments,
            'metadata' => [
                'redactions' => $sanitizedResult['redactions'],
                'detected_adversarial_patterns' => $sanitizedResult['detected_patterns'],
            ],
            'content_hash' => hash('sha256', $normalized),
        ];
    }
}
