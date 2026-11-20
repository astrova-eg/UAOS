<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Parsing;

use UniversalAgentOS\Exceptions\DocumentParsingException;
use UniversalAgentOS\Research\Contracts\DocumentParserInterface;
use UniversalAgentOS\Research\Sanitization\ContentSanitizer;
use UniversalAgentOS\Research\Sanitization\EvidenceSegmenter;

/**
 * Structured JSON parser converting hierarchical API responses into flattened factual segments.
 */
class JsonDocumentParser implements DocumentParserInterface
{
    private const SUPPORTED = ['application/json', 'text/json', 'json'];
    public const MAX_JSON_DEPTH = 10;

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
            throw new DocumentParsingException("JSON document content cannot be empty.");
        }

        $decoded = json_decode($content, true, self::MAX_JSON_DEPTH);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new DocumentParsingException("Invalid or over-deep JSON: " . json_last_error_msg());
        }

        $lines = [];
        $this->flattenArray($decoded, '', $lines);
        $rawText = implode("\n\n", $lines);

        $sanitizedResult = $this->sanitizer->sanitize($rawText);
        $cleanText = $sanitizedResult['sanitized'];

        $segments = $this->segmenter->segment($cleanText);

        return [
            'format' => 'json',
            'title' => is_array($decoded) && isset($decoded['title']) ? (string)$decoded['title'] : '',
            'text' => $cleanText,
            'segments' => $segments,
            'metadata' => [
                'entry_count' => count($lines),
                'redactions' => $sanitizedResult['redactions'],
                'detected_adversarial_patterns' => $sanitizedResult['detected_patterns'],
            ],
            'content_hash' => hash('sha256', $cleanText),
        ];
    }

    /**
     * @param mixed $data
     * @param list<string> $lines
     */
    private function flattenArray(mixed $data, string $prefix, array &$lines, int $depth = 0): void
    {
        if ($depth > self::MAX_JSON_DEPTH) {
            return;
        }

        if (is_array($data)) {
            $isAssoc = array_keys($data) !== range(0, count($data) - 1);
            foreach ($data as $k => $v) {
                $newKey = $prefix === '' ? (string)$k : ($isAssoc ? "{$prefix}.{$k}" : "{$prefix}[{$k}]");
                if (is_array($v)) {
                    $this->flattenArray($v, $newKey, $lines, $depth + 1);
                } else {
                    $strVal = is_bool($v) ? ($v ? 'true' : 'false') : (string)$v;
                    $lines[] = "{$newKey}: {$strVal}";
                }
            }
        } else {
            $lines[] = (string)$data;
        }
    }
}
