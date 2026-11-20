<?php
declare(strict_types=1);

require_once __DIR__ . '/src/SkillEngine.php';
require_once __DIR__ . '/src/Gate4Verifier.php';
require_once __DIR__ . '/src/ToolGateway.php';
require_once __DIR__ . '/src/PromptBuilder.php';
require_once __DIR__ . '/src/EvidenceCollector.php';
require_once __DIR__ . '/src/Orchestrator.php';

use UniversalCore\Runtime\Orchestrator;
use UniversalCore\Runtime\Gate4Verifier;
use UniversalCore\Runtime\SkillEngine;

$command = $argv[1] ?? 'help';

function parseCliArgs(array $argv): array {
    $opts = [];
    for ($i = 2; $i < count($argv); $i++) {
        if (str_starts_with($argv[$i], '--')) {
            $parts = explode('=', substr($argv[$i], 2), 2);
            $key = $parts[0];
            $val = $parts[1] ?? ($argv[$i + 1] ?? true);
            if (!isset($parts[1]) && isset($argv[$i + 1]) && !str_starts_with($argv[$i + 1], '--')) {
                $i++;
            }
            if (isset($opts[$key])) {
                if (!is_array($opts[$key])) {
                    $opts[$key] = [$opts[$key]];
                }
                $opts[$key][] = $val;
            } else {
                $opts[$key] = $val;
            }
        }
    }
    return $opts;
}

$opts = parseCliArgs($argv);
$agentsDir = isset($opts['agents-dir']) ? (string)$opts['agents-dir'] : dirname(__DIR__, 1);
$projectRoot = isset($opts['project-root']) ? (string)$opts['project-root'] : dirname($agentsDir);

switch ($command) {
    case 'run':
        $task = (string)($opts['task'] ?? 'Default diagnostic task');
        $agentId = isset($opts['agent']) ? (string)$opts['agent'] : null;
        $gate4Path = isset($opts['gate4']) ? (string)$opts['gate4'] : null;
        $secret = isset($opts['secret']) ? (string)$opts['secret'] : (getenv('CORE_GATE4_HUMAN_SECRET') ?: null);

        $orchestrator = new Orchestrator($agentsDir);

        $operation = null;
        if (isset($opts['op'])) {
            $operation = [
                'operation' => (string)$opts['op'],
                'target' => (string)($opts['target'] ?? ''),
                'content' => (string)($opts['content'] ?? ''),
                'command' => (string)($opts['cmd'] ?? '')
            ];
        }

        $delegations = [];
        if (isset($opts['delegate'])) {
            $delEntries = is_array($opts['delegate']) ? $opts['delegate'] : [(string)$opts['delegate']];
            foreach ($delEntries as $entry) {
                $parts = explode(':', (string)$entry, 2);
                $delegations[] = [
                    'agent_id' => trim($parts[0]),
                    'task' => trim($parts[1] ?? 'Delegated task')
                ];
            }
        }

        $result = $orchestrator->runTask([
            'task_id' => 'TASK-' . time(),
            'task_description' => $task,
            'preferred_agent_id' => $agentId,
            'gate4_token_path' => $gate4Path,
            'secret_key' => $secret,
            'operation' => $operation,
            'delegations' => $delegations
        ]);

        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        exit($result['status'] === 'COMPLETED' ? 0 : 1);

    case 'verify-gate4':
        $token = (string)($opts['token'] ?? '');
        $secret = isset($opts['secret']) ? (string)$opts['secret'] : (getenv('CORE_GATE4_HUMAN_SECRET') ?: null);
        if ($token === '') {
            fwrite(STDERR, "Error: --token is required.\n");
            exit(1);
        }
        $verifier = new Gate4Verifier($projectRoot);
        $res = $verifier->verifyToken($token, $secret);
        echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        exit($res['valid'] ? 0 : 2);

    case 'discover-skills':
        $agentId = (string)($opts['agent'] ?? 'A11Y-001');
        $role = isset($opts['role']) ? (string)$opts['role'] : null;
        $kw = isset($opts['keyword']) ? [(string)$opts['keyword']] : [];
        $engine = new SkillEngine(
            $agentsDir . '/registry/skill-registry.json',
            $agentsDir . '/skills'
        );
        $skills = $engine->discoverSkillIds($agentId, $role, $kw);
        echo json_encode(['agent' => $agentId, 'discovered_skills' => $skills], JSON_PRETTY_PRINT) . "\n";
        exit(0);

    case 'help':
    default:
        echo "Universal Core Autonomous Agent Runtime CLI\n";
        echo "Usage: php cli.php <command> [options]\n";
        echo "Commands:\n";
        echo "  run --task=\"...\" [--agent=ID] [--op=write_file|read_file|run_command] [--gate4=path]\n";
        echo "  verify-gate4 --token=\"path\" [--secret=\"...\"]\n";
        echo "  discover-skills --agent=ID [--role=\"...\"] [--keyword=\"...\"]\n";
        exit(0);
}
