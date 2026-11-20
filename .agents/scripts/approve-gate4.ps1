<#
.SYNOPSIS
    Gate 4 Human Implementation Authorization Script (HMAC-SHA256)
.DESCRIPTION
    Generates a cryptographically signed Gate 4 Human Implementation Authorization Record.
    Requires human secret key entry, calculates physical prerequisite file SHA-256 hashes,
    and produces an immutable canonical token.
#>
[CmdletBinding()]
param (
    [Parameter(Mandatory = $true)]
    [string]$SystemId,

    [Parameter(Mandatory = $true)]
    [string]$SrsPath,

    [Parameter(Mandatory = $true)]
    [string]$TaskDagPath,

    [Parameter(Mandatory = $true)]
    [string]$PlanPath,

    [Parameter(Mandatory = $false)]
    [string]$Approver = "Human Project Sponsor",

    [Parameter(Mandatory = $false)]
    [string]$ProjectId = "universal-core",

    [Parameter(Mandatory = $false)]
    [string]$GitBaseline = "baseline-clean",

    [Parameter(Mandatory = $false)]
    [string]$SecretKey,

    [Parameter(Mandatory = $false)]
    [int]$ValidityHours = 24,

    [Parameter(Mandatory = $false)]
    [string]$OutputPath
)

$ErrorActionPreference = "Stop"

function Get-FileSha256 {
    param ([string]$Path)
    if (-not (Test-Path -Path $Path -PathType Leaf)) {
        throw "Prerequisite file not found: $Path"
    }
    $hasher = [System.Security.Cryptography.SHA256]::Create()
    $bytes = [System.IO.File]::ReadAllBytes((Resolve-Path $Path).Path)
    $hashBytes = $hasher.ComputeHash($bytes)
    return -join ($hashBytes | ForEach-Object { "{0:x2}" -f $_ })
}

Write-Host "=== GATE 4 HUMAN IMPLEMENTATION AUTHORIZATION ISSUER ===" -ForegroundColor Cyan
Write-Host "Target System: $SystemId"

# 1. Verify Prerequisites and Compute Physical Hashes
Write-Host "Verifying and hashing prerequisite files..."
$srsHash = Get-FileSha256 -Path $SrsPath
$dagHash = Get-FileSha256 -Path $TaskDagPath
$planHash = Get-FileSha256 -Path $PlanPath

Write-Host "  SRS Hash:      $srsHash"
Write-Host "  Task DAG Hash: $dagHash"
Write-Host "  Plan Hash:     $planHash"

# 2. Acquire Human Signing Key (Strict Isolation)
$signingKey = $SecretKey
if ([string]::IsNullOrWhiteSpace($signingKey)) {
    if ($env:CORE_GATE4_HUMAN_SECRET) {
        $signingKey = $env:CORE_GATE4_HUMAN_SECRET
    } else {
        $secureInput = Read-Host "Enter Human HMAC-SHA256 Signing Secret" -AsSecureString
        $bstr = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($secureInput)
        $signingKey = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($bstr)
    }
}

if ([string]::IsNullOrWhiteSpace($signingKey)) {
    throw "Human signing secret is mandatory and cannot be empty."
}

# 3. Assemble Canonical Manifest
$now = [DateTime]::UtcNow
$createdAt = $now.ToString("yyyy-MM-ddTHH:mm:ssZ")
$expiresAt = $now.AddHours($ValidityHours).ToString("yyyy-MM-ddTHH:mm:ssZ")
$nonce = [Guid]::NewGuid().ToString("D")

$manifest = [ordered]@{
    "approver"       = $Approver
    "created_at"     = $createdAt
    "expires_at"     = $expiresAt
    "git_baseline"   = $GitBaseline
    "nonce"          = $nonce
    "plan_hash"      = $planHash
    "plan_path"      = $PlanPath.Replace("\", "/")
    "project_id"     = $ProjectId
    "srs_hash"       = $srsHash
    "srs_path"       = $SrsPath.Replace("\", "/")
    "system_id"      = $SystemId
    "task_dag_hash"  = $dagHash
    "task_dag_path"  = $TaskDagPath.Replace("\", "/")
}

$canonicalString = ($manifest.GetEnumerator() | ForEach-Object { "$($_.Key)=$($_.Value)" }) -join "&"

# 4. Compute HMAC-SHA256 Signature
$keyBytes = [System.Text.Encoding]::UTF8.GetBytes($signingKey)
$hmac = New-Object System.Security.Cryptography.HMACSHA256
$hmac.Key = $keyBytes
$stringBytes = [System.Text.Encoding]::UTF8.GetBytes($canonicalString)
$signatureBytes = $hmac.ComputeHash($stringBytes)
$signature = -join ($signatureBytes | ForEach-Object { "{0:x2}" -f $_ })

# 5. Output Authorization Record
$authRecord = [ordered]@{
    "version"          = "1.0.0"
    "gate"             = "GATE-4"
    "algorithm"        = "HMAC-SHA256"
    "canonical_string" = $canonicalString
    "signature"        = $signature
    "manifest"         = $manifest
}

if ([string]::IsNullOrWhiteSpace($OutputPath)) {
    $authDir = Join-Path (Get-Location) ".agents\authorizations"
    if (-not (Test-Path $authDir)) {
        New-Item -ItemType Directory -Path $authDir -Force | Out-Null
    }
    $OutputPath = Join-Path $authDir "gate4-$SystemId.json"
} else {
    $targetDir = Split-Path $OutputPath -Parent
    if (-not [string]::IsNullOrWhiteSpace($targetDir) -and -not (Test-Path $targetDir)) {
        New-Item -ItemType Directory -Path $targetDir -Force | Out-Null
    }
}

$jsonText = $authRecord | ConvertTo-Json -Depth 5
$utf8NoBom = New-Object System.Text.UTF8Encoding $false
$targetFullFile = Join-Path (Resolve-Path -Path (Split-Path $OutputPath -Parent)).Path (Split-Path $OutputPath -Leaf)
[System.IO.File]::WriteAllText($targetFullFile, $jsonText, $utf8NoBom)

Write-Host "Gate 4 Authorization successfully signed and recorded at:" -ForegroundColor Green
Write-Host "  $OutputPath"
Write-Host "Signature: $signature"
