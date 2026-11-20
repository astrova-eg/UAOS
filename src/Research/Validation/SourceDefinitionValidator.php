<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Validation;

use InvalidArgumentException;
use UniversalAgentOS\Research\Contracts\SourceValidatorInterface;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Models\TimestampValidator;
use UniversalCore\Validation\RdeiSchemaValidator;

/**
 * Production-grade validator for authoritative source definitions.
 * Delegates to canonical RdeiSchemaValidator and enforces additional integrity rules.
 */
class SourceDefinitionValidator implements SourceValidatorInterface
{
    private ?RdeiSchemaValidator $schemaValidator;

    private const ALLOWED_CATEGORIES = [
        SourceDefinition::CATEGORY_KNOWLEDGE_SOURCE,
        SourceDefinition::CATEGORY_DISCOVERY_PROVIDER,
        SourceDefinition::CATEGORY_RETRIEVAL_MECHANISM,
        SourceDefinition::CATEGORY_TOOLING_MCP,
    ];

    private const ALLOWED_DOMAINS = [
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',
        'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'GLOBAL'
    ];

    public function __construct(?RdeiSchemaValidator $schemaValidator = null)
    {
        if ($schemaValidator !== null) {
            $this->schemaValidator = $schemaValidator;
        } else {
            $this->schemaValidator = $this->resolveSchemaValidator();
        }
    }

    public function validateArray(array $data): array
    {
        $errors = [];

        // 1. Delegate to authoritative RdeiSchemaValidator if available
        if ($this->schemaValidator !== null) {
            $errors = $this->schemaValidator->validateSourceDefinition($data);
        }

        // 2. Validate category type
        $category = (string)($data['category_type'] ?? '');
        if (!in_array($category, self::ALLOWED_CATEGORIES, true)) {
            $errors[] = "Invalid category_type '{$category}'. Allowed: " . implode(', ', self::ALLOWED_CATEGORIES);
        }

        // 3. Validate domains
        $domains = $data['domains'] ?? [];
        if (!is_array($domains) || count($domains) === 0) {
            $errors[] = "domains must be a non-empty array of valid domain letters [A-P] or GLOBAL.";
        } else {
            foreach ($domains as $d) {
                if (!is_string($d) || !in_array(strtoupper($d), self::ALLOWED_DOMAINS, true)) {
                    $errors[] = "Invalid domain identifier '{$d}'. Must be one of: " . implode(', ', self::ALLOWED_DOMAINS);
                }
            }
        }

        // 4. Validate duplicate access mechanisms
        $mechanisms = $data['access_mechanisms'] ?? [];
        if (is_array($mechanisms)) {
            $seenEndpoints = [];
            foreach ($mechanisms as $idx => $mech) {
                if (!is_array($mech)) {
                    continue;
                }
                $type = (string)($mech['mechanism_type'] ?? '');
                $url = (string)($mech['endpoint_url'] ?? '');
                $key = $type . '|' . $url;
                if (isset($seenEndpoints[$key])) {
                    $errors[] = "Duplicate access mechanism detected at index {$idx}: type '{$type}' and URL '{$url}' already declared.";
                }
                $seenEndpoints[$key] = true;

                // Validate mechanism timestamp is not in the future
                $vAt = $mech['verified_at'] ?? null;
                if (is_string($vAt) && $vAt !== '') {
                    if (!TimestampValidator::isValid($vAt)) {
                        $errors[] = "Mechanism[{$idx}].verified_at '{$vAt}' is not a valid ISO-8601 calendar datetime.";
                    } else {
                        $ts = strtotime($vAt);
                        if ($ts !== false && $ts > time() + 300) { // allow 5 min clock skew
                            $errors[] = "Mechanism[{$idx}].verified_at '{$vAt}' is in the future.";
                        }
                    }
                }
            }
        }

        // 5. Validate source last_verified_at not in future
        $sourceVAt = $data['last_verified_at'] ?? null;
        if (is_string($sourceVAt) && $sourceVAt !== '') {
            if (!TimestampValidator::isValid($sourceVAt)) {
                $errors[] = "Source last_verified_at '{$sourceVAt}' is not a valid ISO-8601 calendar datetime.";
            } else {
                $ts = strtotime($sourceVAt);
                if ($ts !== false && $ts > time() + 300) {
                    $errors[] = "Source last_verified_at '{$sourceVAt}' is in the future.";
                }
            }
        }

        // 6. Strict Secret Hygiene check on authProfile and access mechanisms
        $auth = $data['auth_profile'] ?? [];
        if (is_array($auth)) {
            $this->assertNoSecretLeaks($auth, 'auth_profile', $errors);
        }
        if (is_array($mechanisms)) {
            foreach ($mechanisms as $idx => $mech) {
                if (is_array($mech) && isset($mech['auth_requirement']) && is_array($mech['auth_requirement'])) {
                    $this->assertNoSecretLeaks($mech['auth_requirement'], "access_mechanisms[{$idx}].auth_requirement", $errors);
                }
            }
        }

        return array_values(array_unique($errors));
    }

    public function validate(SourceDefinition $source): array
    {
        return $this->validateArray($source->toArray());
    }

    public function assertValid(SourceDefinition $source): void
    {
        $errors = $this->validate($source);
        if (count($errors) > 0) {
            throw new InvalidArgumentException(
                "SourceDefinition '{$source->sourceId}' failed schema validation:\n- " . implode("\n- ", $errors)
            );
        }
    }

    private function assertNoSecretLeaks(array $authData, string $context, array &$errors): void
    {
        $forbiddenKeys = ['token', 'password', 'secret', 'api_key', 'private_key', 'bearer'];
        foreach ($forbiddenKeys as $fk) {
            if (isset($authData[$fk])) {
                $errors[] = "{$context} contains forbidden secret field '{$fk}'. Secrets must never be stored in definitions.";
            }
        }

        if (isset($authData['env_var_key']) && is_string($authData['env_var_key'])) {
            $key = $authData['env_var_key'];
            if (!preg_match('/^[A-Z][A-Z0-9_]{1,63}$/', $key)) {
                $errors[] = "{$context}.env_var_key must match ^[A-Z][A-Z0-9_]{1,63}$, got '{$key}'.";
            }
            if (ctype_xdigit($key) && strlen($key) >= 24) {
                $errors[] = "{$context}.env_var_key appears to be a raw hexadecimal secret/hash.";
            }
            $disallowedPrefixes = ['GHP_', 'GLPAT_', 'SK_LIVE_', 'SK_TEST_', 'EYJ0', 'EYJH', 'AIZASY'];
            foreach ($disallowedPrefixes as $prefix) {
                if (str_starts_with($key, $prefix)) {
                    $errors[] = "{$context}.env_var_key contains raw credential prefix '{$prefix}'.";
                }
            }
        }
    }

    private function resolveSchemaValidator(): ?RdeiSchemaValidator
    {
        if (class_exists(RdeiSchemaValidator::class)) {
            return new RdeiSchemaValidator();
        }

        $candidates = [
            dirname(__DIR__, 4) . '/.agents/validation/src/RdeiSchemaValidator.php',
            dirname(__DIR__, 3) . '/.agents/validation/src/RdeiSchemaValidator.php',
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                require_once $candidate;
                if (class_exists(RdeiSchemaValidator::class)) {
                    return new RdeiSchemaValidator();
                }
            }
        }

        return null;
    }
}
