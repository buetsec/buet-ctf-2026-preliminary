# Exploit Scripts

This directory contains the complete exploit for the "Shadow Protocol" CTF challenge.

## Files

- **exploit_complete.py** - Fully automated exploit (recommended)
- **exploit.py** - Original exploit template
- **exploit.sh** - Bash exploit script
- **e.py** - CVE-2021-3129 reference exploit
- **manual_exploit.md** - Step-by-step manual exploitation guide

## Quick Start

```bash
# Run the complete automated exploit
python3 exploit_complete.py http://localhost:5050
```

## What It Does

The exploit chains multiple vulnerabilities:

1. **Leaks APP_KEY** from debug page (`/api/node/status?node_id=invalid`)
2. **Forges signed URL** for `/_ignition/execute-solution` using HMAC-SHA256
3. **Exploits CVE-2021-3129** (facade/ignition 2.5.1) to access `/flag.txt`

## Requirements

```bash
pip3 install requests
```

## Example Output

```
╔═══════════════════════════════════════════════════════════════╗
║   Trust Issues v2: CVE-2021-3129 + Signed URL Chain          ║
╚═══════════════════════════════════════════════════════════════╝

PHASE 1: APP_KEY LEAK
[+] APP_KEY: base64:dGhpc2lzYXNlY3JldGtleWZvcmN0ZmNoYWxsZW5nZSE=

PHASE 2-3: SIGNATURE FORGERY + CVE-2021-3129
[+] Signed URL forged
[+] CVE-2021-3129 exploited successfully!

PHASE 3: FLAG EXTRACTION
[+] Flag retrieved successfully!

=================================================================
SUCCESS!
=================================================================

🚩  BUETCTF{ignition_signature_chain_pwned}

=================================================================
```

## Manual Exploitation

For a step-by-step manual approach, see `manual_exploit.md`.

## Debug Artifacts

The `solv/` directory includes actual HTTP response samples for reference:
- **debug_response.html** - Example Laravel debug page showing the APP_KEY leak
- **exploit_response.txt** - Example error response when testing exploitation paths

These help understand what responses to expect during exploitation.

## Notes

- The exploit works against Laravel 8 with facade/ignition 2.5.1
- Requires APP_DEBUG=true to leak APP_KEY
- The `/_ignition/execute-solution` endpoint is protected by signed URLs
- Players must modify public CVE-2021-3129 exploits to add signature parameters
