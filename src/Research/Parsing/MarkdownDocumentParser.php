<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Parsing;

use UniversalAgentOS\Exceptions\DocumentParsingException;
use UniversalAgentOS\Research\Contracts\DocumentParserInterface;
use UniversalAgentOS\Research\Sanitization\ContentSanitizer;
use UniversalAgentOS\Research\Sanitization\EvidenceSegmenter;

/**
 * Markdown parser normalizing structure, stripping dangerous embedded HTML, and segmenting content.
 */
class MarkdownDocumentParser implements DocumentParserInterface
{
    private const SUPPORTED = ['text/markdown', 'text/x-markdown', 'md', 'markdown'];

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
            throw new DocumentParsingException("Markdown content cannot be empty.");
        }

        $sanitizedResult = $this->sanitizer->sanitize($content);
        $cleanMd = $sanitizedResult['sanitized'];

        // Normalize markdown: strip embedded HTML comments and raw tags
        $strippedHtml = preg_replace('/<!--.*?-->/s', '', $cleanMd);
        $strippedHtml = preg_replace('/<[a-zA-Z\/][^>]*>/', ' ', (string)$strippedHtml);

        // Normalize multiple blank lines
        $normalized = preg_replace('/(\r?\n){3,}/', "\n\n", trim((string)$strippedHtml)) ?? trim((string)$strippedHtml);

        $title = '';
        if (preg_match('/^#\s+(.+)$/m', $normalized, $m)) {
            $title = trim($m[1]);
        }

        $segments = $this->segmenter->segment($normalized);

        return [
            'format' => 'markdown',
            'title' => $title,
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
