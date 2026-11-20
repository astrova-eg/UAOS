<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Sanitization;

/**
 * Sanitizes untrusted external technical documents, neutralizing active executable constructs,
 * adversarial prompt injection patterns, and enforcing strict data-plane isolation boundaries.
 */
class ContentSanitizer
{
    public const REDACTION_TOKEN = '[REDACTED: ADVERSARIAL PATTERN DETECTED]';

    private const ADVERSARIAL_PATTERNS = [
        '/\bignore\s+(all\s+)?(prior|previous|system)\s+instructions\b/i',
        '/\bsystem\s+prompt\s+override\b/i',
        '/\byou\s+are\s+now\s+(an?\s+unrestricted|in\s+developer\s+mode|dan|jailbroken)\b/i',
        '/\bgrant\s+gate\s*[0-4](\s+approval)?\b/i',
        '/\bbypass\s+(gate|governance|verification|authorization)\b/i',
        '/\bexecute\s+immediately\b/i',
        '/\bdisregard\s+(all\s+)?(rules|policies|governance|guardrails)\b/i',
        '/\boverride\s+security\s+controls\b/i',
        '/\bhuman\s+authorization\s+bypassed\b/i',
    ];

    private const DANGEROUS_TAG_PATTERNS = [
        '/<script\b[^>]*>.*?<\/script>/is',
        '/<style\b[^>]*>.*?<\/style>/is',
        '/<iframe\b[^>]*>.*?<\/iframe>/is',
        '/<object\b[^>]*>.*?<\/object>/is',
        '/<embed\b[^>]*>.*?<\/embed>/is',
        '/<applet\b[^>]*>.*?<\/applet>/is',
        '/<svg\b[^>]*>.*?<\/svg>/is',
        '/<meta\b[^>]*>/is',
        '/<link\b[^>]*>/is',
    ];

    /**
     * Sanitizes raw text or document content, stripping malicious constructs and prompt injections.
     *
     * @return array{sanitized: string, redactions: int, detected_patterns: list<string>}
     */
    public function sanitize(string $rawContent): array
    {
        $cleaned = $this->stripDangerousTagsAndAttributes($rawContent);
        $cleaned = $this->normalizeEncodingAndControlChars($cleaned);

        $detected = [];
        $redactionCount = 0;

        foreach (self::ADVERSARIAL_PATTERNS as $pattern) {
            $matchCount = 0;
            $cleaned = preg_replace_callback($pattern, function (array $m) use (&$detected, &$redactionCount) {
                $detected[] = $m[0];
                $redactionCount++;
                return self::REDACTION_TOKEN;
            }, (string)$cleaned, -1, $matchCount);
        }

        return [
            'sanitized' => trim((string)$cleaned),
            'redactions' => $redactionCount,
            'detected_patterns' => $detected,
        ];
    }

    /**
     * Generates a strict XML isolation fence for untrusted research evidence, preventing tag escape.
     */
    public function createUntrustedEvidenceFence(string $content, string $sourceId, string $contentHash): string
    {
        $escapedSourceId = htmlspecialchars($sourceId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $escapedHash = htmlspecialchars($contentHash, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // Prevent fence breakout by escaping closing tags
        $safeContent = str_replace(
            ['</untrusted_research_evidence>', '<untrusted_research_evidence>'],
            ['&lt;/untrusted_research_evidence&gt;', '&lt;untrusted_research_evidence&gt;'],
            $content
        );

        return "<untrusted_research_evidence source_id=\"{$escapedSourceId}\" content_hash=\"{$escapedHash}\">\n"
            . $safeContent . "\n"
            . "</untrusted_research_evidence>";
    }

    private function stripDangerousTagsAndAttributes(string $html): string
    {
        $out = $html;
        foreach (self::DANGEROUS_TAG_PATTERNS as $pat) {
            $out = preg_replace($pat, '', (string)$out) ?? $out;
        }

        // Strip inline event handlers (e.g. onload=, onclick=, onerror=)
        $out = preg_replace('/\son[a-zA-Z]+\s*=\s*(["\']).*?\1/is', '', (string)$out) ?? $out;
        $out = preg_replace('/\son[a-zA-Z]+\s*=\s*[^\s>]+/is', '', (string)$out) ?? $out;

        // Strip javascript: and data: URI links
        $out = preg_replace('/href\s*=\s*(["\'])\s*(javascript|data):.*?\1/is', 'href="#"', (string)$out) ?? $out;
        $out = preg_replace('/src\s*=\s*(["\'])\s*(javascript|data):.*?\1/is', 'src=""', (string)$out) ?? $out;

        return $out;
    }

    private function normalizeEncodingAndControlChars(string $str): string
    {
        // Enforce valid UTF-8
        $clean = mb_convert_encoding($str, 'UTF-8', 'UTF-8');

        // Remove non-printable control characters except newline (\n), carriage return (\r), and tab (\t)
        return (string)preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $clean);
    }
}
