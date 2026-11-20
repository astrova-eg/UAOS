<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Models;

/**
 * Deterministic canonical array normalizer for cryptographic hashing.
 *
 * Recursively canonicalizes associative array keys in natural string sort order
 * while strictly preserving semantically meaningful list (indexed) array order.
 */
final class CanonicalPayloadSorter
{
    /**
     * Recursively canonicalize array keys.
     */
    public static function canonicalize(mixed $data): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        if ($data === [] || array_is_list($data)) {
            // Preserve semantically meaningful list ordering; canonicalize each element
            return array_map(self::canonicalize(...), $data);
        }

        // Associative array: sort keys in natural string order
        ksort($data, SORT_STRING);

        $result = [];
        foreach ($data as $key => $value) {
            $result[$key] = self::canonicalize($value);
        }

        return $result;
    }
}
