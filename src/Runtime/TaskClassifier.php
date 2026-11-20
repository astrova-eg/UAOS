<?php

declare(strict_types=1);

namespace UniversalAgentOS\Runtime;

/**
 * Categorizes task prompt objectives, mapping them to appropriate departments, roles, and stages.
 */
class TaskClassifier
{
    /**
     * @return array<string, mixed> Classification outcome
     */
    public function classify(string $taskPrompt): array
    {
        $prompt = strtolower($taskPrompt);

        // Security / Audit
        if (preg_match('/\b(audit|vulnerability|cve|penetration|gate\s*4|authorization|crypto|token)\b/', $prompt)) {
            return [
                'department_id' => 'dept-m-independent-audit',
                'recommended_agent' => 'AUD-001',
                'stage' => 12,
                'category' => 'security_audit',
            ];
        }

        // Architecture / Design
        if (preg_match('/\b(architecture|design|adr|system\s*requirements|srs|domain)\b/', $prompt)) {
            return [
                'department_id' => 'dept-c-architecture-design',
                'recommended_agent' => 'ARCH-001',
                'stage' => 2,
                'category' => 'architecture',
            ];
        }

        // Database / Schema
        if (preg_match('/\b(database|migration|schema|table|sql|index|query)\b/', $prompt)) {
            return [
                'department_id' => 'dept-e-database-engineering',
                'recommended_agent' => 'DBA-001',
                'stage' => 10,
                'category' => 'database',
            ];
        }

        // Project Discovery / Analysis
        if (preg_match('/\b(discover|discovery|analysis|inspect|requirements|profile)\b/', $prompt)) {
            return [
                'department_id' => 'dept-b-project-discovery',
                'recommended_agent' => 'DISC-001',
                'stage' => 1,
                'category' => 'discovery',
            ];
        }

        // QA / Testing
        if (preg_match('/\b(test|phpunit|pest|qa|assertion|coverage)\b/', $prompt)) {
            return [
                'department_id' => 'dept-j-qa-testing',
                'recommended_agent' => 'UTST-001',
                'stage' => 11,
                'category' => 'testing',
            ];
        }

        // Implementation (Default backend/core)
        return [
            'department_id' => 'dept-d-laravel-php-engineering',
            'recommended_agent' => 'LCOR-001',
            'stage' => 10,
            'category' => 'implementation',
        ];
    }
}
