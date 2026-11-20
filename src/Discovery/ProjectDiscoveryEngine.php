<?php

declare(strict_types=1);

namespace UniversalAgentOS\Discovery;

use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Contracts\ProjectDiscoveryInterface;
use UniversalAgentOS\Discovery\Detectors\ArchitectureDetector;
use UniversalAgentOS\Discovery\Detectors\DatabaseDetector;
use UniversalAgentOS\Discovery\Detectors\FrontendDetector;
use UniversalAgentOS\Discovery\Detectors\TestingFrameworkDetector;

/**
 * Inspection coordinator discovering host project tech facts and producing project-profile.json.
 */
class ProjectDiscoveryEngine implements ProjectDiscoveryInterface
{
    private PathResolverInterface $pathResolver;
    private ArchitectureDetector $archDetector;
    private DatabaseDetector $dbDetector;
    private FrontendDetector $feDetector;
    private TestingFrameworkDetector $testDetector;
    private ProfileGenerator $generator;
    private ProfileValidator $validator;

    public function __construct(PathResolverInterface $pathResolver)
    {
        $this->pathResolver = $pathResolver;
        $this->archDetector = new ArchitectureDetector();
        $this->dbDetector = new DatabaseDetector();
        $this->feDetector = new FrontendDetector();
        $this->testDetector = new TestingFrameworkDetector();
        $this->generator = new ProfileGenerator();
        $this->validator = new ProfileValidator($pathResolver);
    }

    public function discover(string $projectRoot): array
    {
        $composerPath = $projectRoot . '/composer.json';
        $composerRaw = file_exists($composerPath) ? (string)file_get_contents($composerPath) : '';
        $composerData = json_decode($composerRaw, true);

        $backend = 'generic_php';
        $phpVersion = '8.2';

        if (is_array($composerData)) {
            $req = (array)($composerData['require'] ?? []);
            if (isset($req['laravel/framework'])) {
                $backend = 'laravel';
            } elseif (isset($req['symfony/framework-bundle'])) {
                $backend = 'symfony';
            }
            if (isset($req['php'])) {
                $phpVersion = (string)$req['php'];
            }
        }

        return [
            'backend_framework' => [
                'value' => $backend,
                'source' => 'composer.json:require',
                'status' => 'automatically_detected',
            ],
            'php_version' => [
                'value' => $phpVersion,
                'source' => 'composer.json:require.php',
                'status' => 'automatically_detected',
            ],
            'architecture' => $this->archDetector->detect($projectRoot),
            'database' => $this->dbDetector->detect($projectRoot),
            'frontend' => $this->feDetector->detect($projectRoot),
            'testing' => $this->testDetector->detect($projectRoot),
        ];
    }

    public function generateProfile(string $projectRoot, array $manualOverrides = []): array
    {
        $facts = $this->discover($projectRoot);
        $projectName = basename($projectRoot);
        $profile = $this->generator->generate($projectName, $facts, $manualOverrides);

        $targetFile = $this->pathResolver->getProjectStorageDir() . DIRECTORY_SEPARATOR . 'project-profile.json';
        file_put_contents($targetFile, (string)json_encode($profile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $profile;
    }

    public function validateProfile(array $profileData): array
    {
        return $this->validator->validate($profileData);
    }
}
