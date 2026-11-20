<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Sanitization;

/**
 * Segments normalized technical document text into atomic hashed blocks with exact offsets.
 */
class EvidenceSegmenter
{
    public const DEFAULT_MAX_SEGMENT_LENGTH = 4000;
    public const DEFAULT_MIN_SEGMENT_LENGTH = 10;

    public function __construct(
        private int $maxSegmentLength = self::DEFAULT_MAX_SEGMENT_LENGTH,
        private int $minSegmentLength = self::DEFAULT_MIN_SEGMENT_LENGTH
    ) {}

    /**
     * Segments text into structured atomic units with cryptographic hashes and positional offsets.
     *
     * @return array<int, array{index: int, text: string, hash: string, offset_start: int, offset_end: int}>
     */
    public function segment(string $text): array
    {
        $rawSegments = preg_split('/(\r?\n){2,}/', $text, -1, PREG_SPLIT_OFFSET_CAPTURE);
        if ($rawSegments === false) {
            return [];
        }

        $results = [];
        $seq = 0;

        foreach ($rawSegments as $entry) {
            [$chunk, $offset] = $entry;
            $trimmed = trim((string)$chunk);
            $len = mb_strlen($trimmed);

            if ($len < $this->minSegmentLength) {
                continue;
            }

            // If a paragraph exceeds maximum length, partition into sub-chunks
            if ($len > $this->maxSegmentLength) {
                $subChunks = $this->partitionLongChunk($trimmed, (int)$offset);
                foreach ($subChunks as $sub) {
                    $results[] = [
                        'index' => $seq++,
                        'text' => $sub['text'],
                        'hash' => hash('sha256', $sub['text']),
                        'offset_start' => $sub['offset_start'],
                        'offset_end' => $sub['offset_end'],
                    ];
                }
            } else {
                $results[] = [
                    'index' => $seq++,
                    'text' => $trimmed,
                    'hash' => hash('sha256', $trimmed),
                    'offset_start' => (int)$offset,
                    'offset_end' => (int)$offset + strlen($trimmed),
                ];
            }
        }

        return $results;
    }

    /**
     * @return array<int, array{text: string, offset_start: int, offset_end: int}>
     */
    private function partitionLongChunk(string $chunk, int $baseOffset): array
    {
        $sub = [];
        $sentences = preg_split('/(?<=[.?!])\s+/', $chunk, -1, PREG_SPLIT_OFFSET_CAPTURE);
        if ($sentences === false || empty($sentences)) {
            $sentences = [[$chunk, 0]];
        }

        $current = '';
        $currentStart = $baseOffset;

        foreach ($sentences as $s) {
            [$sentText, $sentOff] = $s;
            if (mb_strlen($current) + mb_strlen($sentText) > $this->maxSegmentLength && $current !== '') {
                $sub[] = [
                    'text' => trim($current),
                    'offset_start' => $currentStart,
                    'offset_end' => $currentStart + strlen($current),
                ];
                $current = $sentText;
                $currentStart = $baseOffset + (int)$sentOff;
            } else {
                $current = $current === '' ? $sentText : $current . ' ' . $sentText;
            }
        }

        if (trim($current) !== '') {
            $sub[] = [
                'text' => trim($current),
                'offset_start' => $currentStart,
                'offset_end' => $currentStart + strlen($current),
            ];
        }

        return $sub;
    }
}
