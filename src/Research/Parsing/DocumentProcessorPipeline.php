<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Parsing;

use UniversalAgentOS\Exceptions\DocumentParsingException;
use UniversalAgentOS\Research\Contracts\DocumentParserInterface;

/**
 * Processing pipeline coordinating format detection, size guards, and format-specific parsing.
 */
class DocumentProcessorPipeline
{
    public const DEFAULT_MAX_BYTES = 2097152; // 2MB

    /** @var array<int, DocumentParserInterface> */
    private array $parsers = [];

    /**
     * @param array<int, DocumentParserInterface>|null $parsers
     */
    public function __construct(
        ?array $parsers = null,
        private int $maxBytes = self::DEFAULT_MAX_BYTES
    ) {
        if ($parsers !== null) {
            $this->parsers = $parsers;
        } else {
            $this->registerDefaultParsers();
        }
    }

    public function registerParser(DocumentParserInterface $parser): void
    {
        $this->parsers[] = $parser;
    }

    /**
     * @return array<int, DocumentParserInterface>
     */
    public function getParsers(): array
    {
        return $this->parsers;
    }

    /**
     * Processes a retrieved document payload or content string, validating size and delegating to supported parser.
     *
     * @param array<string, mixed>|string $document
     * @return array<string, mixed>
     */
    public function process(array|string $document, ?string $hintFormat = null): array
    {
        $content = is_array($document) ? (string)($document['content'] ?? '') : $document;
        if (strlen($content) > $this->maxBytes) {
            throw new DocumentParsingException("Document payload exceeds configured size limit of {$this->maxBytes} bytes");
        }

        $format = $this->detectFormat($document, $hintFormat);
        $parser = $this->resolveParser($format);

        $parsed = $parser->parse($document);
        if (is_array($document)) {
            $parsed['source_id'] = $document['source_id'] ?? null;
            $parsed['url'] = $document['url'] ?? null;
            $parsed['retrieved_at'] = $document['retrieved_at'] ?? null;
            $parsed['payload_hash'] = $document['hash'] ?? hash('sha256', $content);
        }

        return $parsed;
    }

    public function detectFormat(array|string $document, ?string $hintFormat = null): string
    {
        if ($hintFormat !== null && trim($hintFormat) !== '') {
            return strtolower(trim($hintFormat));
        }

        if (is_array($document)) {
            $mime = (string)($document['mime_type'] ?? '');
            if ($mime !== '') {
                $baseMime = strtolower(trim(explode(';', $mime)[0]));
                if ($baseMime !== 'application/octet-stream' && $baseMime !== '') {
                    return $baseMime;
                }
            }

            $url = (string)($document['url'] ?? '');
            if ($url !== '') {
                $path = parse_url($url, PHP_URL_PATH);
                if (is_string($path) && $path !== '') {
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    if ($ext !== '') {
                        return $ext;
                    }
                }
            }
        }

        $content = trim(is_array($document) ? (string)($document['content'] ?? '') : $document);
        if (str_starts_with($content, '{') || str_starts_with($content, '[')) {
            return 'application/json';
        }
        if (str_starts_with($content, '<!DOCTYPE html') || str_starts_with($content, '<html') || str_contains(substr($content, 0, 500), '<body')) {
            return 'text/html';
        }
        if (str_starts_with($content, '<?xml') || str_starts_with($content, '<rss') || str_starts_with($content, '<feed')) {
            return 'application/xml';
        }
        if (str_starts_with($content, '# ') || str_contains(substr($content, 0, 500), "\n## ")) {
            return 'text/markdown';
        }

        return 'text/plain';
    }

    public function resolveParser(string $format): DocumentParserInterface
    {
        foreach ($this->parsers as $p) {
            if ($p->supportsFormat($format)) {
                return $p;
            }
        }
        throw new DocumentParsingException("Unsupported document format or MIME type: '{$format}'");
    }

    private function registerDefaultParsers(): void
    {
        $this->parsers = [
            new HtmlDocumentParser(),
            new MarkdownDocumentParser(),
            new JsonDocumentParser(),
            new XmlDocumentParser(),
            new PlainTextDocumentParser(),
        ];
    }
}
