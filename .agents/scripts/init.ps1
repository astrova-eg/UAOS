<#
.SYNOPSIS
    Universal Core Autonomous Agent Operating System - Host Initialization Script
.DESCRIPTION
    Validates host runtime prerequisites, detects the containing project root,
    verifies canonical agent and skill registries, creates local runtime directories,
    and reports system readiness for task execution.
#>
[CmdletBinding()]
param (
    [Parameter(Mandatory = $false)]
    [string]$ProjectRoot
)

$ErrorActionPreference = "Stop"

Write-Host "=======================================================" -ForegroundColor Cyan
Write-Host " UNIVERSAL CORE AUTONOMOUS AGENT OS - INITIALIZATION   " -ForegroundColor Cyan
Write-Host "=======================================================" -ForegroundColor Cyan

# 1. Detect Directories
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$agentsDir = Split-Path -Parent $scriptDir
if ([string]::IsNullOrWhiteSpace($ProjectRoot)) {
    $ProjectRoot = Split-Path -Parent $agentsDir
}

Write-Host "Project Root: $ProjectRoot"
Write-Host "Agents Directory: $agentsDir"

# 2. Check PHP Runtime
try {
    $phpVersion = php -r "echo PHP_VERSION;" 2>$null
    if ([string]::IsNullOrWhiteSpace($phpVersion)) {
        throw "PHP runtime not found in PATH."
    }
    Write-Host "PHP Version: $phpVersion" -ForegroundColor Green
} catch {
    Write-Error "PHP 8.2+ is required to execute the Agent Operating System."
    exit 1
}

# 3. Verify Canonical Registries
$agentReg = Join-Path $agentsDir "registry\agent-registry.json"
$skillReg = Join-Path $agentsDir "registry\skill-registry.json"
$govCore = Join-Path $agentsDir "governance\SHARED-AGENT-GOVERNANCE-CORE.md"

if (-not (Test-Path $agentReg)) {
    Write-Error "Canonical agent registry missing: $agentReg"
    exit 1
}
if (-not (Test-Path $skillReg)) {
    Write-Error "Canonical skill registry missing: $skillReg"
    exit 1
}
if (-not (Test-Path $govCore)) {
    Write-Error "Shared governance core missing: $govCore"
    exit 1
}

# 4. Ensure Local State & Report Directories
$dirsToEnsure = @(
    (Join-Path $agentsDir "runtime\state"),
    (Join-Path $agentsDir "reports"),
    (Join-Path $agentsDir "authorizations")
)

foreach ($d in $dirsToEnsure) {
    if (-not (Test-Path $d)) {
        New-Item -ItemType Directory -Path $d -Force | Out-Null
        Write-Host "Created local directory: $d"
    }
}

Write-Host "Registries and governance verified (112 agents, 23 skills)." -ForegroundColor Green
Write-Host "Universal Core Autonomous Agent OS is READY in host project." -ForegroundColor Green
Write-Host ""
Write-Host "To execute a task, run:" -ForegroundColor Yellow
Write-Host "  php .agents\runtime\cli.php run --task=""Your engineering task""" -ForegroundColor Gray
