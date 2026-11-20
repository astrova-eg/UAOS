<?php

declare(strict_types=1);

namespace UniversalAgentOS\Runtime;

/**
 * Routes engineering deliverables to Department M independent audit agents based on separation of duties.
 */
class ReviewRouter
{
    /**
     * Resolves the independent Department M auditor assigned to review the active agent's output.
     */
    public function routeReview(string $authorAgentId, int $stage): string
    {
        if ($stage === 4) {
            return 'RVAL-001';
        }
        if ($stage === 6) {
            return 'STD-001'; // Gate 2: STD-001 reviews ORCH-001
        }
        if ($stage === 8) {
            return 'ARCH-001'; // Gate 3: ARCH-001 reviews LARCH-001
        }
        if ($stage >= 10 && $stage <= 12) {
            return 'AUD-008'; // Gate 5: Dept M Auditor
        }
        if ($stage === 13) {
            return 'ACPT-001'; // Acceptance DoD
        }

        return 'AUD-001';
    }

    /**
     * Checks if two agents violate the Separation of Duties matrix.
     */
    public function violatesSeparationOfDuties(string $creatorAgentId, string $auditorAgentId): bool
    {
        // Creator cannot be their own auditor
        if (strcasecmp($creatorAgentId, $auditorAgentId) === 0) {
            return true;
        }

        // Department M auditors cannot audit other Department M auditors without escalation
        if (str_starts_with(strtoupper($creatorAgentId), 'AUD') && str_starts_with(strtoupper($auditorAgentId), 'AUD')) {
            return true;
        }

        return false;
    }
}
