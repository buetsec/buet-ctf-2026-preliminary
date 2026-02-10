## Smart Home

Author: [@sakibulalikhan](https://x.com/sakibulalikhan)

Difficulty: MEDIUM

Description:
You’re auditing a local smart‑home portal used by households to control lights, climate, and door locks.

Remote:

- URL: `http://<host>:7513/`

File: [Smart_Home.zip](Smat_Home.zip)

Flag format: buetctf{fl4g}

---

### Solution

This challenge recreates **CVE-2025-68271 (OpenC3 COSMOS)**: a JSON-RPC “string command” is parsed unsafely, where array-like inputs can trigger Ruby code execution before authorization.

Exploit: send `POST /api/rpc` (method `cmd`) with `command: "ALARM ARM [activity_log(File.read(\"/flag.txt\"))]"`.

The injected code reads `/flag.txt` and writes it into the activity log.
Retrieve the flag from the dashboard Activity panel or `GET /api/activity`.

Full writeup: [Writeup of Smart Home By Sakibulalikhan](https://sakibulalikhan.medium.com/smart-home-buet-ctf-2026-preliminary-pre-auth-rce-via-eval-in-a-json-rpc-command-api-d414da3fcebe)
