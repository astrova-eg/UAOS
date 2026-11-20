<?php

declare(strict_types=1);

namespace UniversalAgentOS\Contracts;

/**
 * Value object declaring host IDE / platform capabilities and exact enforcement boundaries.
 */
readonly class HostCapabilityMap
{
    public const ENFORCEMENT_RUNTIME = 'Runtime-enforced';
    public const ENFORCEMENT_ADAPTER = 'Adapter-enforced';
    public const ENFORCEMENT_HOST = 'Host-enforced';
    public const ENFORCEMENT_OS = 'Operating-system-enforced';
    public const ENFORCEMENT_ADVISORY = 'Non-guaranteed / advisory only';

    /**
     * @param bool $supportsNativeSubagents Subagent spawning supported natively
     * @param bool $supportsMcpTools Model Context Protocol supported natively
     * @param bool $supportsToolInterception Pre-execution interception supported
     * @param bool $supportsReadOnlyScoping Scoping tools read-only at invocation
     * @param bool $supportsInteractivePrompts Interactive human prompt confirmation
     * @param bool $supportsMultiTurnStreaming Multi-turn conversational streaming
     * @param string $primaryEnforcementCategory One of the five approved categories
     * @param array<int, string> $supportedToolOperations List of supported operations
     * @param array<int, string> $unsupportedOperations Explicitly unsupported operations
     * @param array<string, string> $knownBypassPaths Documented bypass paths
     */
    public function __construct(
        public bool $supportsNativeSubagents,
        public bool $supportsMcpTools,
        public bool $supportsToolInterception,
        public bool $supportsReadOnlyScoping,
        public bool $supportsInteractivePrompts,
        public bool $supportsMultiTurnStreaming,
        public string $primaryEnforcementCategory,
        public array $supportedToolOperations = [],
        public array $unsupportedOperations = [],
        public array $knownBypassPaths = []
    ) {
    }

    /**
     * Converts capability map to associative array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'supports_native_subagents' => $this->supportsNativeSubagents,
            'supports_mcp_tools' => $this->supportsMcpTools,
            'supports_tool_interception' => $this->supportsToolInterception,
            'supports_read_only_scoping' => $this->supportsReadOnlyScoping,
            'supports_interactive_prompts' => $this->supportsInteractivePrompts,
            'supports_multi_turn_streaming' => $this->supportsMultiTurnStreaming,
            'primary_enforcement_category' => $this->primaryEnforcementCategory,
            'supported_tool_operations' => $this->supportedToolOperations,
            'unsupported_operations' => $this->unsupportedOperations,
            'known_bypass_paths' => $this->knownBypassPaths,
        ];
    }
}
