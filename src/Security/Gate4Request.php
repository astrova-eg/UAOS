<?php

declare(strict_types=1);

namespace UniversalAgentOS\Security;

/**
 * Value object representing an authorization request submitted to Gate 4.
 */
readonly class Gate4Request
{
    /**
     * @param string $projectId Authoritative project identifier
     * @param string $projectRoot Project root directory
     * @param string $taskId Unique authoritative task identifier
     * @param int $stage Current workflow stage requesting clearance (e.g. 4)
     * @param int $clearedStage Target workflow stage authorized (e.g. 10)
     * @param string $operation Requested operation ('WRITE', 'EXECUTE', 'MIGRATE')
     * @param array<int, string> $targetArtifacts List of file paths to modify or create
     * @param array<string, string> $prerequisiteArtifacts Map of prerequisite files to check
     * @param int $ttlSeconds Expiration window in seconds (default 3600)
     */
    public function __construct(
        public string $projectId,
        public string $projectRoot,
        public string $taskId,
        public int $stage = 4,
        public int $clearedStage = 10,
        public string $operation = 'WRITE',
        public array $targetArtifacts = [],
        public array $prerequisiteArtifacts = [],
        public int $ttlSeconds = 3600
    ) {
    }
}
