# Solution of The Missing Piece

## Challenge Overview

Standard RSA setup with 1024-bit primes `p` and `q`. The server gives us:

- `n = p * q`
- `x = (p ^ q) % (1 << 532)` — the lower 532 bits of `p XOR q`
- `c = pow(m, 65537, n)` — RSA encryption of a random 256-byte secret

We must decrypt `c` and send back `m` to get the flag.

## Approach

The solve has two phases:

### Phase 1: Recover lower bits of `p` and `q` via BFS

We know `n mod 2^532` and `(p XOR q) mod 2^532`. We can reconstruct the lower 532 bits of `p` and `q` one bit at a time using a BFS/branch-and-prune strategy:

- Start with the LSB: both `p` and `q` must be odd (since they're prime), so `p₀ = q₀ = 1`.
- At each bit position `k`, try extending every existing `(p_partial, q_partial)` candidate by setting the k-th bit to 0 or 1 for both `p` and `q`.
- Prune candidates that violate either constraint:
  - `(p_partial * q_partial) mod 2^k == n mod 2^k` (product constraint)
  - `(p_partial XOR q_partial) mod 2^k == x mod 2^k` (XOR constraint)

This produces a set of candidate `(p_low, q_low)` pairs — the lower 532 bits.

**Note on candidate count:** The number of surviving candidates can vary wildly between instances — sometimes under 300, sometimes over 2,000. The solve script retries the connection if the count exceeds 500 to get a more tractable instance. Waiting ~2-5 attempts usually yields an instance with fewer than 500 candidates.

### Phase 2: Recover full `p` via Coppersmith's method

With 532 out of 1024 bits of `p` known (>50%), we can use Coppersmith's small roots method (lattice-based) to recover the remaining upper bits.

For each candidate `p_low` from Phase 1, we construct the polynomial:

```
f(x) = x * 2^532 + p_low  (mod n)
```

and find small roots. If a root exists, we recover the full `p`, compute `q = n / p`, and decrypt.

**Coppersmith parameter notes: (Credit to [@Tsumii](https://tsumiiiiiiii.github.io/))**
- `beta` and `epsilon` significantly impact both success rate and runtime.
- `epsilon = 1/55` is roughly optimal; `1/80` causes exponential blowup in lattice reduction time, while `1/54` fails on some instances.
- `beta = 0.49` is more robust than `0.5`, which fails in some edge cases.
- Each Coppersmith call takes a few seconds on average, so keeping the candidate count low is important for total runtime.

During testing, I used `beta = 0.44` and `epsilon = 1/64` which worked well.

### Putting it together

1. Connect to the server, receive `n`, `x`, `c`.
2. Run Phase 1 (BFS). If candidates > 500, reconnect for a new instance.
3. Run Phase 2 (Coppersmith) on each candidate until `p` is found.
4. Compute `d`, decrypt `m`.
5. Send `m` to the server to get the flag.
6. The running time of this script is between 40-60 minutes on average, depending on the number of candidate solutions for the second coppersmith stage.

Full solve script: [solv.sage](solv.sage)