<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Contracts;

/**
 * Parser contract for processing raw retrieved document payloads into sanitized structured content.
 *
 * Implemented by format-specific parsers (HTML, Markdown, JSON, XML, PlainText)
 * operating within the content sanitization and isolation boundary.
 */
interface DocumentParserInterface
{
    /**
     * Evaluates whether this parser supports the given MIME type or file extension.
     *
     * @param string $mimeOrExtension MIME type (e.g. 'text/html') or file extension (e.g. 'md')
     */
    public function supportsFormat(string $mimeOrExtension): bool;

    /**
     * Parses raw document payload or content string into structured text segments and metadata.
     *
     * @param array<string, mixed>|string $document Raw document payload or content string
     * @return array<string, mixed> Structured parsed document (e.g. text, segments, metadata, format)
     */
    public function parse(array|string $document): array;
}
