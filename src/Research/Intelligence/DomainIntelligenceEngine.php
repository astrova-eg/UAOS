<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Intelligence;

use InvalidArgumentException;
use UniversalAgentOS\Research\Contracts\DomainIntelligenceInterface;
use UniversalAgentOS\Research\Models\DepthProfile;
use UniversalAgentOS\Research\Models\DomainStrategy;

/**
 * Domain-aware research intelligence engine implementing the authoritative 16-domain taxonomy (A-P).
 */
class DomainIntelligenceEngine implements DomainIntelligenceInterface
{
    /** @var array<string, DomainStrategy> */
    private array $catalog;

    public function __construct()
    {
        $this->catalog = $this->buildAuthoritativeCatalog();
    }

    public function getDomainStrategy(string $domainCode): DomainStrategy
    {
        $code = strtoupper(trim($domainCode));
        if (!isset($this->catalog[$code])) {
            throw new InvalidArgumentException("Unknown or unsupported domain code '{$domainCode}'. Allowed: A through P.");
        }
        return $this->catalog[$code];
    }

    public function getAllDomains(): array
    {
        return $this->catalog;
    }

    public function resolveMultiDomainStrategy(array $domainCodes): array
    {
        if (empty($domainCodes)) {
            $base = $this->catalog['A'];
            return [
                'resolved_domains' => ['A'],
                'primary_source_categories' => $base->primarySourceCategories,
                'minimum_depth' => $base->minimumDepth,
                'minimum_quorum' => $base->minimumQuorum,
                'minimum_ttl_hours' => $base->defaultTtlHours,
                'combined_verification_rules' => $base->verificationRules,
                'applied_overlays' => [$base->overlayPolicy],
                'strictness_notes' => ['Defaulted to Domain A (base software engineering).'],
                'unknown_domains' => [],
            ];
        }

        $resolved = [];
        $unknown = [];
        $sources = [];
        $rules = [];
        $overlays = [];
        $depths = [];
        $quorums = [];
        $ttls = [];

        foreach ($domainCodes as $rawCode) {
            $code = strtoupper(trim($rawCode));
            if (!isset($this->catalog[$code])) {
                $unknown[] = $rawCode;
                continue;
            }
            $strategy = $this->catalog[$code];
            $resolved[$code] = $strategy;
            $sources = array_merge($sources, $strategy->primarySourceCategories);
            $rules = array_merge($rules, $strategy->verificationRules);
            $overlays[] = $strategy->overlayPolicy;
            $depths[] = $strategy->minimumDepth;
            $quorums[] = $strategy->minimumQuorum;
            $ttls[] = $strategy->defaultTtlHours;
        }

        if (empty($resolved)) {
            $base = $this->catalog['A'];
            return [
                'resolved_domains' => ['A'],
                'primary_source_categories' => $base->primarySourceCategories,
                'minimum_depth' => DepthProfile::LEVEL_L2,
                'minimum_quorum' => 2,
                'minimum_ttl_hours' => 168,
                'combined_verification_rules' => $base->verificationRules,
                'applied_overlays' => ['pol-base-global'],
                'strictness_notes' => ['All provided domains were unknown; safely fell back to pol-base-global.'],
                'unknown_domains' => $unknown,
            ];
        }

        $maxDepth = $this->highestDepth($depths);
        $maxQuorum = !empty($quorums) ? max($quorums) : 2;
        $minTtl = !empty($ttls) ? min($ttls) : 168; // strictness: shortest TTL

        $notes = [];
        if (isset($resolved['B'])) $notes[] = 'Domain B active: strict 4-tier CVE applicability & CVSS blocking apply.';
        if (isset($resolved['C'])) $notes[] = 'Domain C active: strict NIST/RFC standard dominance and constant-time audit apply.';
        if (isset($resolved['D'])) $notes[] = 'Domain D active: zero-trust tenant isolation boundaries strictly enforced.';
        if (isset($resolved['H'])) $notes[] = 'Domain H active: clinical preprints prohibited from guideline claims.';
        if (isset($resolved['I'])) $notes[] = 'Domain I active: statutory in-force validity & territorial jurisdiction enforced.';

        return [
            'resolved_domains' => array_keys($resolved),
            'primary_source_categories' => array_values(array_unique($sources)),
            'minimum_depth' => $maxDepth,
            'minimum_quorum' => $maxQuorum,
            'minimum_ttl_hours' => $minTtl,
            'combined_verification_rules' => array_values(array_unique($rules)),
            'applied_overlays' => array_values(array_unique($overlays)),
            'strictness_notes' => $notes,
            'unknown_domains' => $unknown,
        ];
    }

    public function classifyDomainsFromPrompt(string $prompt): array
    {
        $lp = strtolower($prompt);
        $hits = [];
        $map = [
            'A' => ['software', 'code', 'php', 'laravel', 'architecture', 'refactor', 'api', 'framework', 'database', 'git'],
            'B' => ['security', 'vulnerability', 'cve', 'exploit', 'threat', 'pentest', 'firewall', 'soc2', 'owasp', 'jwt'],
            'C' => ['crypto', 'encryption', 'cipher', 'hash', 'signature', 'tls', 'pki', 'argon2', 'aes', 'rsa', 'secret'],
            'D' => ['multi-tenant', 'tenant', 'isolation', 'sandbox', 'rls', 'namespace', 'container', 'jail', 'seccomp'],
            'E' => ['ai', 'machine learning', 'llm', 'rag', 'embedding', 'prompt injection', 'model card', 'neural'],
            'F' => ['math', 'mathematics', 'statistics', 'probability', 'linear algebra', 'numerical', 'optimization'],
            'G' => ['physics', 'chemistry', 'biology', 'materials', 'si units', 'experimental dataset', 'laboratory'],
            'H' => ['medicine', 'clinical', 'healthcare', 'fda', 'drug', 'patient', 'disease', 'trial', 'treatment'],
            'I' => ['law', 'legal', 'gdpr', 'compliance', 'statute', 'regulation', 'copyright', 'license', 'treaty'],
            'J' => ['economics', 'finance', 'accounting', 'tax', 'banking', 'inflation', 'gdp', 'ifrs', 'gaap', 'audit'],
            'K' => ['engineering', 'industrial', 'iec 61508', 'hazop', 'plc', 'scada', 'robotics', 'quality engineering'],
            'L' => ['energy', 'agriculture', 'climate', 'environment', 'ipcc', 'solar', 'carbon', 'haccp'],
            'M' => ['education', 'linguistics', 'pedagogy', 'curriculum', 'history', 'philosophy', 'lexicon'],
            'N' => ['government', 'census', 'public data', 'gazette', 'oecd', 'united nations', 'world bank'],
            'O' => ['management', 'project management', 'pmbok', 'prince2', 'agile', 'scrum', 'itil', 'operations'],
            'P' => ['quantum computing', 'synthetic biology', 'crispr', 'space tech', 'bci', 'fusion', 'emerging'],
        ];

        foreach ($map as $domain => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($lp, $kw)) {
                    $hits[] = $domain;
                    break;
                }
            }
        }
        return empty($hits) ? ['A'] : array_values(array_unique($hits));
    }

    /**
     * @return array<string, DomainStrategy>
     */
    private function buildAuthoritativeCatalog(): array
    {
        return [
            'A' => new DomainStrategy('A', 'Software Engineering & Computer Science', 'formal_and_empirical', ['official_documentation', 'official_release_notes', 'source_code_repository', 'package_repository'], DepthProfile::LEVEL_L2, 2, 168, ['semver_release_alignment', 'active_support_window_check', 'upstream_framework_override'], 'pol-overlay-software-arch'),
            'B' => new DomainStrategy('B', 'Cybersecurity & Information Security', 'adversarial_and_empirical', ['vulnerability_database', 'security_advisory', 'standards_organization'], DepthProfile::LEVEL_L3, 3, 6, ['four_tier_vulnerability_applicability', 'cisa_kev_epss_escalation', 'cvss_threshold_gate_blocking'], 'pol-overlay-cybersecurity'),
            'C' => new DomainStrategy('C', 'Cryptography & Data Protection', 'formal_mathematical_standards', ['standards_organization', 'official_documentation', 'scientific_publication'], DepthProfile::LEVEL_L4, 3, 720, ['standard_dominance_nist_rfc_iso', 'side_channel_constant_time_audit', 'strict_rejection_of_proprietary_ciphers'], 'pol-overlay-cryptography'),
            'D' => new DomainStrategy('D', 'Isolation, Multi-Tenancy & Access Boundaries', 'systems_architecture_and_security', ['official_documentation', 'standards_organization', 'source_code_repository'], DepthProfile::LEVEL_L4, 3, 168, ['tenant_leakage_boundary_proof', 'row_level_security_enforcement', 'zero_trust_least_privilege'], 'pol-overlay-isolation'),
            'E' => new DomainStrategy('E', 'Artificial Intelligence & Machine Learning', 'empirical_and_statistical', ['scientific_publication', 'official_documentation', 'source_code_repository'], DepthProfile::LEVEL_L2, 2, 168, ['academic_benchmark_reproducibility', 'prompt_injection_defense_in_depth', 'model_card_transparency'], 'pol-base-global'),
            'F' => new DomainStrategy('F', 'Mathematics & Statistics', 'pure_formal_mathematics', ['scientific_publication', 'professional_institution'], DepthProfile::LEVEL_L2, 2, 720, ['formal_mathematical_proof_rigor', 'numerical_precision_bounds', 'ams_classification_alignment'], 'pol-overlay-empirical-sciences'),
            'G' => new DomainStrategy('G', 'Physics, Chemistry & Natural Sciences', 'empirical_and_experimental', ['scientific_publication', 'standards_organization'], DepthProfile::LEVEL_L2, 2, 720, ['peer_reviewed_mandatory_gating', 'crossref_retraction_checking', 'si_metrology_standards'], 'pol-overlay-empirical-sciences'),
            'H' => new DomainStrategy('H', 'Medicine, Healthcare & Life Sciences', 'clinical_evidence_and_regulatory', ['scientific_publication', 'professional_institution', 'official_documentation'], DepthProfile::LEVEL_L3, 3, 168, ['methodological_evidence_hierarchy_grade', 'clinical_status_gating_no_preprints', 'strict_jurisdictional_bindingness_fda_ema'], 'pol-overlay-medicine-clinical'),
            'I' => new DomainStrategy('I', 'Law, Regulations & Compliance', 'normative_and_statutory', ['standards_organization', 'official_documentation', 'professional_institution'], DepthProfile::LEVEL_L3, 2, 720, ['normative_hierarchy_legislation_over_guidance', 'temporal_validity_in_force_sunset_check', 'strict_territorial_jurisdiction_boundary'], 'pol-overlay-statutory-law'),
            'J' => new DomainStrategy('J', 'Economics, Finance & Business', 'empirical_economic_statistics', ['official_documentation', 'standards_organization', 'professional_institution'], DepthProfile::LEVEL_L2, 2, 24, ['primary_statistical_hierarchy_nso_cb_override', 'preliminary_vs_revised_vintage_tracking', 'gaap_ifrs_standards_compliance'], 'pol-overlay-macroeconomics'),
            'K' => new DomainStrategy('K', 'Engineering & Industrial Fields', 'applied_physical_engineering', ['standards_organization', 'professional_institution', 'official_documentation'], DepthProfile::LEVEL_L3, 2, 720, ['functional_safety_iec_61508', 'hazard_analysis_hazop_verification', 'consensus_standards_iso_ieee_asme'], 'pol-overlay-enterprise-governance'),
            'L' => new DomainStrategy('L', 'Energy, Agriculture & Environment', 'applied_environmental_science', ['standards_organization', 'scientific_publication', 'official_documentation'], DepthProfile::LEVEL_L2, 2, 168, ['ipcc_climate_data_alignment', 'iso_14001_sustainability_standards', 'grid_safety_interconnection_rules'], 'pol-base-global'),
            'M' => new DomainStrategy('M', 'Education, Languages & Humanities', 'humanities_and_linguistics', ['scientific_publication', 'professional_institution'], DepthProfile::LEVEL_L1, 2, 720, ['peer_reviewed_pedagogical_rigor', 'standardized_lexicon_corpus_provenance', 'curriculum_framework_alignment'], 'pol-base-global'),
            'N' => new DomainStrategy('N', 'Government, Public Data & International Organizations', 'official_public_records', ['official_documentation', 'standards_organization'], DepthProfile::LEVEL_L2, 2, 168, ['official_gazette_registry_validation', 'multilateral_institution_provenance', 'demographic_census_currency'], 'pol-overlay-macroeconomics'),
            'O' => new DomainStrategy('O', 'Management & Professional Services', 'professional_body_of_knowledge', ['standards_organization', 'professional_institution', 'independent_research'], DepthProfile::LEVEL_L2, 2, 720, ['established_bok_pmbok_prince2_iso31000', 'consulting_papers_relegated_to_tier4', 'enterprise_risk_framework_conformance'], 'pol-overlay-enterprise-governance'),
            'P' => new DomainStrategy('P', 'Additional & Emerging Domains', 'frontier_science_and_technology', ['scientific_publication', 'standards_organization', 'source_code_repository'], DepthProfile::LEVEL_L3, 3, 168, ['peer_reviewed_preprints_scrutiny', 'hardware_specification_verification', 'high_uncertainty_rejection_of_unverified_claims'], 'pol-base-global'),
        ];
    }

    /**
     * @param array<int, string> $depths
     */
    private function highestDepth(array $depths): string
    {
        $rank = [
            DepthProfile::LEVEL_L0 => 0,
            DepthProfile::LEVEL_L1 => 1,
            DepthProfile::LEVEL_L2 => 2,
            DepthProfile::LEVEL_L3 => 3,
            DepthProfile::LEVEL_L4 => 4,
        ];
        $maxRank = 0;
        $maxLvl = DepthProfile::LEVEL_L1;
        foreach ($depths as $d) {
            $r = $rank[$d] ?? 1;
            if ($r > $maxRank) {
                $maxRank = $r;
                $maxLvl = $d;
            }
        }
        return $maxLvl;
    }
}
