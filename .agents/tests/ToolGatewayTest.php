<?php
declare(strict_types=1);

namespace UniversalCore\Tests;

require_once __DIR__ . '/Assert.php';
require_once dirname(__DIR__) . '/runtime/src/ToolGateway.php';
require_once dirname(__DIR__) . '/runtime/src/Gate4Verifier.php';

use UniversalCore\Runtime\ToolGateway;

final class ToolGatewayTest
{
    public static function run(): void
    {
        echo "Running ToolGatewayTest...\n";
        $gateway = new ToolGateway();
        $baseDir = dirname(__DIR__);

        // 1. Authorized read
        $readReq = [
            'agent_id' => 'CORE-001',
            'department' => 'DEPT-D',
            'operation' => 'read_file',
            'target' => '.agents/registry/matrix-authority-permissions.json'
        ];
        $readRes = $gateway->evaluateAndExecute($readReq);
        Assert::assertTrue($readRes['permitted'] && $readRes['executed'], "ToolGatewayTest: Authorized file read succeeds");

        // 2. Authorized harmless write within sandbox/reports
        $testReportPath = '.agents/reports/gateway-test-output.txt';
        $writeReq = [
            'agent_id' => 'QA-001',
            'department' => 'DEPT-J',
            'operation' => 'write_file',
            'target' => $testReportPath,
            'content' => 'Safe test output'
        ];
        $writeRes = $gateway->evaluateAndExecute($writeReq);
        Assert::assertTrue($writeRes['permitted'] && $writeRes['executed'], "ToolGatewayTest: Authorized file write in reports succeeds");

        // 3. Unauthorized write to governance by non-Dept A agent
        $govWriteReq = [
            'agent_id' => 'CORE-001',
            'department' => 'DEPT-D',
            'operation' => 'write_file',
            'target' => '.agents/governance/ENGINEERING-CONSTITUTION.md',
            'content' => 'Compromised content'
        ];
        $govRes = $gateway->evaluateAndExecute($govWriteReq);
        Assert::assertFalse($govRes['permitted'], "ToolGatewayTest: Unauthorized governance write blocked");

        // 4. Missing Gate 4 authorization for Stage 10 code modification
        $codeReq = [
            'agent_id' => 'CORE-001',
            'department' => 'DEPT-D',
            'stage' => 10,
            'operation' => 'write_file',
            'target' => 'src/BusinessService.php',
            'content' => '<?php echo "code";'
        ];
        $codeRes = $gateway->evaluateAndExecute($codeReq);
        Assert::assertFalse($codeRes['permitted'], "ToolGatewayTest: Stage 10 code write blocked without Gate 4 token");

        // 5. Destructive shell command blocked
        $shellReq = [
            'agent_id' => 'DEVOPS-001',
            'department' => 'DEPT-L',
            'operation' => 'run_command',
            'command' => 'rm -rf /'
        ];
        $shellRes = $gateway->evaluateAndExecute($shellReq);
        Assert::assertFalse($shellRes['permitted'], "ToolGatewayTest: Dangerous destructive shell command blocked");

        // 6. Attempted Gate 4 self-approval blocked
        $selfApproveReq = [
            'agent_id' => 'ORCH-001',
            'department' => 'DEPT-A',
            'operation' => 'approve_gate4'
        ];
        $selfRes = $gateway->evaluateAndExecute($selfApproveReq);
        Assert::assertFalse($selfRes['permitted'], "ToolGatewayTest: Agent Gate 4 self-approval attempt blocked");

        // Clean up test file
        $fullPath = $baseDir . '/reports/gateway-test-output.txt';
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}
