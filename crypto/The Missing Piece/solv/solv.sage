import math
from sage import *
import time
from tqdm import tqdm
from pwn import *

def long_to_bytes(n):
    byte_length = (n.bit_length() + 7) // 8
    return n.to_bytes(byte_length, 'big')

def check_cong(k, p, q, n, xored=None):
    kmask = (1 << k) - 1
    p &= kmask
    q &= kmask
    n &= kmask
    pqm = (p*q) & kmask
    return pqm == n and (xored is None or (p^^q) == (xored & kmask))

def extend(k, a):
    kbit = 1 << (k-1)
    assert a < kbit
    yield a
    yield a | kbit

def factor(n, p_xor_q, max_iterations, stop_at_midpoint=False):
    tracked = set([(p, q) for p in [0, 1] for q in [0, 1]
                  if check_cong(1, p, q, n, p_xor_q)])

    maxtracked = len(tracked)
    print('Initial tracked set size: {}'.format(len(tracked)))

    pbar = tqdm(range(2, max_iterations+1))
    for k in pbar:
        newset = set()
        for tp, tq in tracked:
            for newp_ in extend(k, tp):
                for newq_ in extend(k, tq):
                    # Remove symmetry
                    newp, newq = sorted([newp_, newq_])
                    if check_cong(k, newp, newq, n, p_xor_q):
                        newset.add((newp, newq))

        tracked = newset
        if len(tracked) > maxtracked:
            maxtracked = len(tracked)
        pbar.set_description(f"Tracked size = {len(tracked)}")

    print('Tracked set size: {} (max={}) at {} bits'.format(len(tracked), maxtracked, max_iterations))

    # If we stopped early, return the partial candidates
    if stop_at_midpoint:
        print('Stopped at midpoint. Returning {} partial candidates.'.format(len(tracked)))
        return tracked

    # go through the tracked set and pick the correct (p, q)
    for p, q in tracked:
        if p != 1 and p*q == n:
            return p, q

    assert False, 'factors were not in tracked set. Is your p^q correct?'

def coppersmith_lsb_known(N, a0, prime_bits, known_bits):
    # a0: known lower bits of p
    # prime_bits: total bits of p
    # known_bits: number of known lower bits

    sizep = prime_bits
    p_lsb = a0
    R = 2**known_bits
    invR = inverse_mod(R, N)

    F = PolynomialRing(Zmod(N), names=('x',))
    x = F.gen()
    f = x + p_lsb * invR

    roots = f.small_roots(X=2**(sizep-known_bits)-1, beta=0.44, epsilon=1/64)
    if not roots:
        return None

    x0 = Integer(roots[0])
    p = x0 * R + p_lsb
    if N % p == 0:
        return p
    return None

HOST = "localhost"
PORT = 6101
PRIME_BITS = 1024
known_bits = 532
MAX_CANDIDATES = 500

def recv_values(r):
    line_n = r.recvline().decode().strip()
    line_x = r.recvline().decode().strip()
    line_c = r.recvline().decode().strip()

    n = int(line_n.split("=")[-1].strip())
    x = int(line_x.split("=")[-1].strip())
    c = int(line_c.split("=")[-1].strip())

    return n, x, c

attempt = 0
while True:
    attempt += 1
    print(f"\n{'='*60}")
    print(f"Attempt {attempt}: Connecting to {HOST}:{PORT}")
    print(f"{'='*60}")

    r = remote(HOST, PORT)
    n, x, c = recv_values(r)

    n_masked = n & ((1 << known_bits) - 1)

    factors = factor(n_masked, x, max_iterations=known_bits, stop_at_midpoint=True)
    num_candidates = len(factors)
    print(f"Number of candidates at midpoint: {num_candidates}")

    if num_candidates > MAX_CANDIDATES:
        print(f"Too many candidates ({num_candidates} > {MAX_CANDIDATES}), retrying with new values...")
        r.close()
        continue

    print(f"Candidate count {num_candidates} <= {MAX_CANDIDATES}, proceeding with Coppersmith...")
    break

start_time = time.time()

for k in tqdm(list(factors)):
    min_val = int(min(k[0], k[1]))
    p = coppersmith_lsb_known(n, min_val, PRIME_BITS, known_bits)
    if p is not None:
        end_time = time.time()
        print(f"Found factors in {end_time - start_time:.2f} seconds")
        print("Recovered p:", p)
        q = n // p
        print("Recovered q:", q)
        print("Check p*q == n:", p * q == n)
        d = inverse_mod(65537, (p-1)*(q-1))
        guess = pow(c, d, n)
        print("Guess:", guess)

        r.sendlineafter(b": ", str(guess).encode())
        response = r.recvline().decode().strip()
        print(f"Flag: {response}")
        r.close()
        break
