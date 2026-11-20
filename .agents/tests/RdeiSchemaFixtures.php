<?php
declare(strict_types=1);

namespace UniversalCore\Tests;

final class RdeiSchemaFixtures
{
    public static function getValidSourceDef(): array
    {
        return [
            'source_id' => 'src-test-01',
            'official_name' => 'Official Test Source Repository',
            'description' => 'Authoritative test repository for schema validation and unit testing.',
            'owner' => [
                'institution_name' => 'Test Standards Organization',
                'institution_type' => 'standards_body',
                'jurisdiction' => 'GLOBAL'
            ],
            'canonical_url' => 'https://test.standards.org',
            'domains' => ['A'],
            'subdomains' => ['A.01'],
            'authority_tier' => 1,
            'source_designation' => 'primary_authority',
            'category_type' => 'knowledge_source',
            'supported_languages' => ['en'],
            'supported_formats' => ['application/json'],
            'access_mechanisms' => [
                [
                    'mechanism_type' => 'rest_api',
                    'endpoint_url' => 'https://api.test.standards.org/v1',
                    'is_active' => true,
                    'verification_status' => 'docs_verified',
                    'evidence_reference' => 'Test Standards Spec v1.0',
                    'verified_at' => '2026-11-15T18:00:00Z'
                ]
            ],
            'auth_profile' => [
                'requires_auth' => true,
                'auth_type' => 'api_key_header',
                'env_var_key' => 'TEST_API_KEY'
            ],
            'cost_tier' => 'free_public',
            'licensing' => [
                'license_type' => 'Open Data License',
                'reuse_permitted' => true,
                'attribution_required' => false
            ],
            'rate_limits' => [
                'requests_per_minute' => 60,
                'burst_limit' => 10,
                'daily_quota' => 10000
            ],
            'freshness_policy' => [
                'max_cache_ttl_hours' => 24,
                'revalidation_trigger' => 'daily_24h'
            ],
            'status' => 'active',
            'last_verified_at' => '2026-11-15T18:00:00Z',
            'classification_evidence' => 'Institutional charter published under international treaty.',
            'verification_profile' => [
                'website_verified' => true,
                'api_doc_verified' => true,
                'endpoint_verified' => true,
                'connectivity_tested' => false,
                'auth_tested' => false,
                'quota_confirmed' => true,
                'licensing_reviewed' => true,
                'verification_date' => '2026-11-15T18:00:00Z',
                'verification_evidence' => 'Docs verified via portal.',
                'limitations' => 'No live network test executed in testing phase.'
            ],
            'independence_profile' => [
                'parent_institution' => 'Test Standards Org',
                'is_syndicated' => false,
                'syndication_upstream' => [],
                'citation_dependencies' => [],
                'is_mirror' => false
            ],
            'applicability_profile' => [
                'requires_component_verification' => true,
                'requires_version_check' => true,
                'requires_config_verification' => false
            ],
            'provenance_rules' => [
                'retains_upstream_url' => true,
                'retains_publisher_identity' => true,
                'hashes_raw_payload' => true
            ],
            'fallback_source_ids' => []
        ];
    }

    public static function getValidDomainOverlay(): array
    {
        return [
            'policy_id' => 'pol-overlay-cybersecurity',
            'name' => 'Cybersecurity Domain Policy Overlay',
            'target_domain' => 'B',
            'target_subdomains' => ['*'],
            'version' => '1.0.0',
            'parent_policy_id' => 'pol-base-global',
            'precedence_rank' => 50,
            'methodological_hierarchy' => ['primary_statute', 'official_cve', 'vendor_bulletin'],
            'context_match_rules' => [
                'require_exact_version_match' => true,
                'jurisdiction_strictness' => 'global_advisory',
                'eol_handling' => 'block_gate_0'
            ],
            'vulnerability_applicability_rules' => [
                'enforce_component_identity' => true,
                'enforce_version_range' => true,
                'enforce_config_reachability' => true,
                'cisa_kev_escalation' => true,
                'cvss_critical_threshold' => 7.0
            ],
            'conflict_escalation_rules' => [
                [
                    'condition' => 'CVE severity dispute between NVD and primary vendor advisory',
                    'escalate_to' => 'CRITICAL_DISPUTE',
                    'gate_0_action' => 'BLOCK'
                ]
            ],
            'freshness_overrides' => [
                'default_ttl_hours' => 6,
                'max_ttl_hours' => 24,
                'emergency_ttl_hours' => 1
            ],
            'mandatory_quorum' => 2
        ];
    }

    public static function getValidSubdomainOverlay(): array
    {
        $d = self::getValidDomainOverlay();
        $d['policy_id'] = 'pol-overlay-sec-cve';
        $d['name'] = 'Vulnerability Management Subdomain Policy Overlay';
        $d['target_subdomains'] = ['B.02'];
        $d['parent_policy_id'] = 'pol-overlay-cybersecurity';
        $d['precedence_rank'] = 100;
        return $d;
    }

    public static function getGlobalBasePolicyExample(): array
    {
        $d = self::getValidDomainOverlay();
        $d['policy_id'] = 'pol-base-global';
        $d['name'] = 'UAOS RDEI Base Global Policy';
        $d['target_domain'] = 'GLOBAL';
        $d['target_subdomains'] = ['*'];
        $d['parent_policy_id'] = 'none';
        $d['precedence_rank'] = 0;
        return $d;
    }

    public static function getNvdExample(): array
    {
        $src = self::getValidSourceDef();
        $src['source_id'] = 'src-sec-nvd';
        $src['official_name'] = 'National Vulnerability Database';
        $src['domains'] = ['B'];
        $src['subdomains'] = ['B.02', 'B.04'];
        $src['canonical_url'] = 'https://nvd.nist.gov';
        $src['access_mechanisms'] = [
            [
                'mechanism_type' => 'rest_api',
                'endpoint_url' => 'https://services.nvd.nist.gov/rest/json/cves/2.0',
                'is_active' => true,
                'verification_status' => 'docs_verified',
                'evidence_reference' => 'NVD API v2.0 Official Specification and NIST CSRC Portal',
                'verified_at' => '2026-11-15T18:00:00Z',
                'auth_requirement' => [
                    'requires_auth' => true,
                    'auth_type' => 'inherit'
                ]
            ],
            [
                'mechanism_type' => 'rest_api',
                'endpoint_url' => 'https://services.nvd.nist.gov/rest/json/cves/2.0',
                'is_active' => true,
                'verification_status' => 'docs_verified',
                'evidence_reference' => 'NVD API v2.0 Public Unauthenticated Tier Documentation',
                'verified_at' => '2026-11-15T18:00:00Z',
                'auth_requirement' => [
                    'requires_auth' => false,
                    'auth_type' => 'none'
                ]
            ]
        ];
        return $src;
    }

    public static function getEurLexExample(): array
    {
        $src = self::getValidSourceDef();
        $src['source_id'] = 'src-law-eurlex';
        $src['official_name'] = 'EUR-Lex (Access to European Union law)';
        $src['domains'] = ['I'];
        $src['subdomains'] = ['I.02', 'I.06'];
        $src['canonical_url'] = 'https://eur-lex.europa.eu';
        $src['auth_profile'] = [
            'requires_auth' => false,
            'auth_type' => 'none'
        ];
        $src['access_mechanisms'] = [
            [
                'mechanism_type' => 'http_direct',
                'endpoint_url' => 'https://eur-lex.europa.eu/legal-content/EN/TXT/?uri=CELEX:',
                'is_active' => true,
                'verification_status' => 'docs_verified',
                'evidence_reference' => 'EUR-Lex Official Web Services and Portal Documentation',
                'verified_at' => '2026-11-15T18:00:00Z',
                'auth_requirement' => [
                    'requires_auth' => false,
                    'auth_type' => 'none'
                ]
            ]
        ];
        return $src;
    }
}
