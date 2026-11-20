<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use InvalidArgumentException;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Validation\SourceDefinitionValidator;
use UniversalAgentOS\Tests\Assert;
use UniversalCore\Tests\RdeiSchemaFixtures;

require_once __DIR__ . '/../../.agents/tests/RdeiSchemaFixtures.php';

class SourceDefinitionValidationTest
{
    public static function run(): void
    {
        $validator = new SourceDefinitionValidator();
        $validFixture = RdeiSchemaFixtures::getValidSourceDef();

        // 1. Valid source passes completely
        $source = SourceDefinition::fromArray($validFixture);
        $errors = $validator->validate($source);
        Assert::assertCount(0, $errors, "Valid fixture produces 0 validation errors");
        $validator->assertValid($source);

        // 2. Invalid category_type rejected
        $badCategory = $validFixture;
        $badCategory['category_type'] = 'arbitrary_invalid_category';
        $errors = $validator->validateArray($badCategory);
        Assert::assertTrue(count($errors) > 0, "Rejects invalid category_type");

        // 3. Invalid domain rejected
        $badDomain = $validFixture;
        $badDomain['domains'] = ['Z'];
        $errors = $validator->validateArray($badDomain);
        Assert::assertTrue(count($errors) > 0, "Rejects invalid domain letter Z");

        // 4. Duplicate access mechanisms rejected
        $dupMechs = $validFixture;
        $dupMechs['access_mechanisms'][] = $dupMechs['access_mechanisms'][0];
        $errors = $validator->validateArray($dupMechs);
        Assert::assertTrue(count($errors) > 0, "Rejects duplicate access mechanism declaration");

        // 5. Future timestamp rejected
        $futureDef = $validFixture;
        $futureDef['last_verified_at'] = '2099-01-01T00:00:00Z';
        $errors = $validator->validateArray($futureDef);
        Assert::assertTrue(count($errors) > 0, "Rejects future source last_verified_at");

        // 6. Mechanism verified_at newer than source last_verified_at rejected
        $timeDesync = $validFixture;
        $timeDesync['last_verified_at'] = '2026-11-10T00:00:00Z';
        $timeDesync['access_mechanisms'][0]['verified_at'] = '2026-11-15T00:00:00Z';
        $errors = $validator->validateArray($timeDesync);
        Assert::assertTrue(count($errors) > 0, "Rejects mechanism verified_at newer than source last_verified_at");

        // 7. Missing evidence_reference on mechanism rejected
        $noEvidence = $validFixture;
        $noEvidence['access_mechanisms'][0]['evidence_reference'] = '   ';
        $errors = $validator->validateArray($noEvidence);
        Assert::assertTrue(count($errors) > 0, "Rejects empty mechanism evidence_reference");

        // 8. Secret field leaks rejected
        $secretLeak = $validFixture;
        $secretLeak['auth_profile']['token'] = 'raw_api_secret_token';
        $errors = $validator->validateArray($secretLeak);
        Assert::assertTrue(count($errors) > 0, "Rejects raw token field in auth_profile");

        // 9. Raw credential prefix in env_var_key rejected
        $badKeyPrefix = $validFixture;
        $badKeyPrefix['auth_profile']['env_var_key'] = 'GHP_mygithubpersonaltoken';
        $errors = $validator->validateArray($badKeyPrefix);
        Assert::assertTrue(count($errors) > 0, "Rejects raw token prefix GHP_ in env_var_key");

        // 10. Per-mechanism auth override requires_auth=false forbidding env_var_key
        $mechAuthConflict = $validFixture;
        $mechAuthConflict['access_mechanisms'][0]['auth_requirement'] = [
            'requires_auth' => false,
            'auth_type' => 'none',
            'env_var_key' => 'UNWANTED_ENV_KEY',
        ];
        $errors = $validator->validateArray($mechAuthConflict);
        Assert::assertTrue(count($errors) > 0, "Rejects env_var_key on mechanism with requires_auth=false");

        // 11. assertValid throws on failure
        $badSource = new SourceDefinition(
            sourceId: 'src-test-bad',
            officialName: 'Bad Source',
            description: 'Bad Source Description',
            owner: [],
            canonicalUrl: 'https://test.bad',
            domains: ['Z'], // invalid domain
            subdomains: ['Z.01'],
            authorityTier: 3
        );
        Assert::assertThrows(InvalidArgumentException::class, static function () use ($validator, $badSource) {
            $validator->assertValid($badSource);
        }, "assertValid() throws on invalid domain Z");
    }
}
