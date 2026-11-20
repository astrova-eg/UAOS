<?php
declare(strict_types=1);

namespace UniversalCore\Validation;

final class RdeiSchemaValidator
{
    /** @return list<string> Validation error messages (empty if valid) */
    public function validateSourceDefinition(array $data): array
    {
        $errors = [];
        $required = [
            'source_id', 'official_name', 'description', 'owner', 'canonical_url', 'domains',
            'subdomains', 'authority_tier', 'source_designation', 'category_type', 'supported_languages',
            'supported_formats', 'access_mechanisms', 'auth_profile', 'cost_tier', 'licensing',
            'rate_limits', 'freshness_policy', 'status', 'last_verified_at', 'classification_evidence',
            'verification_profile', 'independence_profile', 'applicability_profile', 'provenance_rules', 'fallback_source_ids'
        ];
        foreach ($required as $f) {
            if (!array_key_exists($f, $data)) {
                $errors[] = "Missing required top-level property: '{$f}'";
            }
        }
        if ($errors !== []) {
            return $errors;
        }

        if (!preg_match('/^src-[a-z0-9\-]+$/', (string)$data['source_id'])) {
            $errors[] = "Invalid source_id format: '{$data['source_id']}'";
        }
        if (!preg_match('/^https:\/\/[a-zA-Z0-9.-]+(?:\/.*)?$/', (string)$data['canonical_url'])) {
            $errors[] = "canonical_url must be a remote HTTPS URL: '{$data['canonical_url']}'";
        }
        if (!is_int($data['authority_tier']) || $data['authority_tier'] < 1 || $data['authority_tier'] > 6) {
            $errors[] = "authority_tier must be an integer between 1 and 6";
        }

        // Validate Source Timestamp
        $sourceTime = self::parseIso8601Timestamp($data['last_verified_at'] ?? null);
        if ($sourceTime === null) {
            $errors[] = "source last_verified_at must be valid ISO 8601 datetime: '" . (string)($data['last_verified_at'] ?? '') . "'";
        }

        // Validate Auth Profile
        $auth = $data['auth_profile'];
        if (!is_array($auth) || !isset($auth['requires_auth'], $auth['auth_type'])) {
            $errors[] = "auth_profile must define requires_auth and auth_type";
        } elseif ($auth['requires_auth'] === true) {
            if ($auth['auth_type'] === 'none') {
                $errors[] = "auth_profile requires_auth=true forbids auth_type='none'";
            }
            if (empty($auth['env_var_key']) || !preg_match('/^[A-Z0-9_]+$/', (string)$auth['env_var_key'])) {
                $errors[] = "auth_profile requires valid env_var_key when requires_auth=true";
            }
        } elseif ($auth['requires_auth'] === false) {
            if ($auth['auth_type'] !== 'none') {
                $errors[] = "auth_profile requires_auth=false forces auth_type='none'";
            }
            if (isset($auth['env_var_key'])) {
                $errors[] = "auth_profile requires_auth=false forbids env_var_key";
            }
        }

        // Validate Access Mechanisms
        $mechanisms = $data['access_mechanisms'];
        if (!is_array($mechanisms) || count($mechanisms) < 1) {
            $errors[] = "access_mechanisms must be a non-empty array";
        } else {
            $allowedStatuses = ['untested', 'docs_verified', 'endpoint_verified', 'connectivity_tested', 'fully_tested'];
            foreach ($mechanisms as $idx => $m) {
                if (!is_array($m)) {
                    $errors[] = "access_mechanisms[{$idx}] must be an object";
                    continue;
                }
                foreach (['mechanism_type', 'endpoint_url', 'is_active', 'verification_status', 'evidence_reference', 'verified_at'] as $rf) {
                    if (!isset($m[$rf])) {
                        $errors[] = "access_mechanisms[{$idx}] missing required property '{$rf}'";
                    }
                }
                if (isset($m['verification_status']) && !in_array($m['verification_status'], $allowedStatuses, true)) {
                    $errors[] = "access_mechanisms[{$idx}] has invalid verification_status '{$m['verification_status']}'";
                }
                if (isset($m['evidence_reference']) && (!is_string($m['evidence_reference']) || strlen(trim($m['evidence_reference'])) < 5)) {
                    $errors[] = "access_mechanisms[{$idx}] evidence_reference must be at least 5 chars";
                }
                
                $mechTime = self::parseIso8601Timestamp($m['verified_at'] ?? null);
                if ($mechTime === null) {
                    $errors[] = "access_mechanisms[{$idx}] verified_at must be valid ISO 8601 datetime: '" . (string)($m['verified_at'] ?? '') . "'";
                } elseif ($sourceTime !== null && $mechTime > $sourceTime) {
                    $errors[] = "access_mechanisms[{$idx}] verified_at is more recent than source last_verified_at";
                }

                if (isset($m['auth_requirement'])) {
                    $ar = $m['auth_requirement'];
                    if (!is_array($ar) || !isset($ar['requires_auth'])) {
                        $errors[] = "access_mechanisms[{$idx}].auth_requirement must contain requires_auth";
                    } elseif ($ar['requires_auth'] === false) {
                        if (($ar['auth_type'] ?? 'none') !== 'none' || isset($ar['env_var_key'])) {
                            $errors[] = "access_mechanisms[{$idx}].auth_requirement requires_auth=false forbids env_var_key and non-none auth_type";
                        }
                    } elseif ($ar['requires_auth'] === true && ($ar['auth_type'] ?? 'none') === 'none') {
                        $errors[] = "access_mechanisms[{$idx}].auth_requirement requires_auth=true forbids auth_type='none'";
                    }
                }
            }
        }

        // Validate Verification Profile Consistency
        $vp = $data['verification_profile'] ?? null;
        if (is_array($vp)) {
            if (($vp['auth_tested'] ?? false) === true && ($vp['connectivity_tested'] ?? false) !== true) {
                $errors[] = "verification_profile: auth_tested=true contradicts connectivity_tested=false";
            }
            if (($vp['connectivity_tested'] ?? false) === true && ($vp['endpoint_verified'] ?? false) !== true) {
                $errors[] = "verification_profile: connectivity_tested=true contradicts endpoint_verified=false";
            }
            if (is_array($mechanisms)) {
                foreach ($mechanisms as $idx => $m) {
                    if (is_array($m)) {
                        $st = $m['verification_status'] ?? '';
                        if (in_array($st, ['connectivity_tested', 'fully_tested'], true) && ($vp['connectivity_tested'] ?? false) !== true) {
                            $errors[] = "access_mechanisms[{$idx}] claims '{$st}' but verification_profile.connectivity_tested is false";
                        }
                        if ($st === 'fully_tested' && ($vp['endpoint_verified'] ?? false) !== true) {
                            $errors[] = "access_mechanisms[{$idx}] claims 'fully_tested' but verification_profile.endpoint_verified is false";
                        }
                    }
                }
            }
        }
        return $errors;
    }

    /** @return list<string> Validation error messages (empty if valid) */
    public function validateEvidenceOverlay(array $data): array
    {
        $errors = [];
        $required = [
            'policy_id', 'name', 'target_domain', 'target_subdomains', 'version',
            'parent_policy_id', 'precedence_rank', 'methodological_hierarchy',
            'context_match_rules', 'vulnerability_applicability_rules',
            'conflict_escalation_rules', 'freshness_overrides', 'mandatory_quorum'
        ];
        foreach ($required as $f) {
            if (!array_key_exists($f, $data)) {
                $errors[] = "Missing required overlay property: '{$f}'";
            }
        }
        if ($errors !== []) {
            return $errors;
        }

        $policyId = (string)$data['policy_id'];
        $targetDomain = (string)$data['target_domain'];
        $rank = $data['precedence_rank'];
        $subdomains = $data['target_subdomains'];

        if (!preg_match('/^(pol-overlay-[a-z0-9\-]+|pol-base-global)$/', $policyId)) {
            $errors[] = "Invalid policy_id format: '{$policyId}'";
        }
        if (!preg_match('/^([A-P]|GLOBAL)$/', $targetDomain)) {
            $errors[] = "target_domain must be A-P or GLOBAL: '{$targetDomain}'";
        }
        if (!in_array($rank, [0, 50, 100], true)) {
            $errors[] = "precedence_rank must be exactly 0, 50, or 100 (got {$rank})";
        }

        // Strict SemVer regex
        $semver = '/^(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?$/';
        if (!preg_match($semver, (string)$data['version'])) {
            $errors[] = "version must strictly follow SemVer 2.0.0 format: '{$data['version']}'";
        }

        // Strict GLOBAL and Policy Identity constraints
        if ($targetDomain === 'GLOBAL') {
            if ($rank !== 0) {
                $errors[] = "target_domain 'GLOBAL' requires precedence_rank 0 (got {$rank})";
            }
            if ($policyId !== 'pol-base-global') {
                $errors[] = "target_domain 'GLOBAL' requires policy_id 'pol-base-global' (got '{$policyId}')";
            }
            if ($subdomains !== ['*']) {
                $errors[] = "target_domain 'GLOBAL' requires target_subdomains ['*']";
            }
        } else {
            if ($policyId === 'pol-base-global') {
                $errors[] = "policy_id 'pol-base-global' is only permitted for target_domain 'GLOBAL'";
            }
            if ($rank === 0) {
                $errors[] = "precedence_rank 0 is only permitted for target_domain 'GLOBAL'";
            }
        }
        if (str_starts_with($policyId, 'pol-overlay-') && $targetDomain === 'GLOBAL') {
            $errors[] = "pol-overlay-* policies cannot target domain 'GLOBAL'";
        }

        // Subdomain consistency
        if (!is_array($subdomains) || count($subdomains) < 1) {
            $errors[] = "target_subdomains must be a non-empty array";
        } else {
            if (count($subdomains) !== count(array_unique($subdomains))) {
                $errors[] = "target_subdomains contains duplicate entries";
            }
            if (in_array('*', $subdomains, true) && count($subdomains) > 1) {
                $errors[] = "Wildcard '*' cannot be combined with specific subdomains";
            }
            foreach ($subdomains as $s) {
                if ($s !== '*') {
                    if ($targetDomain === 'GLOBAL') {
                        $errors[] = "GLOBAL target_domain permits only wildcard '*' subdomain";
                    } elseif (!preg_match('/^' . preg_quote($targetDomain, '/') . '\.[0-9]{2}$/', (string)$s)) {
                        $errors[] = "Subdomain '{$s}' does not belong to target_domain '{$targetDomain}'";
                    }
                }
            }
            if ($rank === 50 && $subdomains !== ['*']) {
                $errors[] = "Domain overlay (precedence 50) must target wildcard '*' subdomains";
            }
            if ($rank === 100 && in_array('*', $subdomains, true)) {
                $errors[] = "Subdomain overlay (precedence 100) cannot target wildcard '*'; must target explicit subdomains";
            }
        }

        // Freshness consistency
        $fo = $data['freshness_overrides'] ?? null;
        if (is_array($fo)) {
            $d = $fo['default_ttl_hours'] ?? null;
            $m = $fo['max_ttl_hours'] ?? null;
            $e = $fo['emergency_ttl_hours'] ?? null;
            if (is_int($d) && is_int($m) && $d > $m) {
                $errors[] = "default_ttl_hours ({$d}) cannot exceed max_ttl_hours ({$m})";
            }
            if (is_int($e) && is_int($d) && $e > $d) {
                $errors[] = "emergency_ttl_hours ({$e}) cannot exceed default_ttl_hours ({$d})";
            }
        }

        if (isset($data['parent_policy_id']) && $data['parent_policy_id'] === $policyId) {
            $errors[] = "Direct self-inheritance detected: policy_id equals parent_policy_id";
        }
        return $errors;
    }

    /**
     * Detects missing parents and inheritance cycles in a policy set.
     * @param list<array<string, mixed>> $policies
     * @return list<string>
     */
    public function validatePolicyGraph(array $policies): array
    {
        $errors = [];
        $policyMap = [];
        foreach ($policies as $p) {
            $id = (string)($p['policy_id'] ?? '');
            if ($id !== '') {
                $policyMap[$id] = $p;
            }
        }

        foreach ($policies as $p) {
            $id = (string)($p['policy_id'] ?? '');
            $parentId = $p['parent_policy_id'] ?? null;
            if ($parentId === $id) {
                $errors[] = "Policy '{$id}' directly inherits from itself";
                continue;
            }
            if ($parentId !== null && $parentId !== 'pol-base-global' && $parentId !== 'none' && !isset($policyMap[$parentId])) {
                $errors[] = "Policy '{$id}' references missing parent policy '{$parentId}'";
            }

            $visited = [$id => true];
            $curr = $parentId;
            while ($curr !== null && $curr !== 'pol-base-global' && $curr !== 'none') {
                if (isset($visited[$curr])) {
                    $errors[] = "Inheritance cycle detected: policy '{$id}' encounters cycle at '{$curr}'";
                    break;
                }
                $visited[$curr] = true;
                $curr = $policyMap[$curr]['parent_policy_id'] ?? null;
            }
        }
        return $errors;
    }

    public static function parseIso8601Timestamp(mixed $value): ?int
    {
        if (!is_string($value) || $value === '' || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/', $value)) {
            return null;
        }
        $ts = strtotime($value);
        return $ts !== false ? $ts : null;
    }
}
