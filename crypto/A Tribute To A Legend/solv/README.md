# Solution of A Tribute To A Legend

## Challenge Setup

We are given $N = pqr$ where $p, q, r$ are 1024-bit primes, along with four ciphertexts:

$$ct_1 = m^e \bmod N$$

$$ct_2 = m^s \bmod N$$

$$ct_3 = m^{s^2} \bmod N$$

$$ct_4 = m^{ss} \bmod N$$

where $s = p + q + r$, $ss = p^2 + q^2 + r^2$, and $e = 65537$.

The goal is to recover the secret $m$.

## Step 1: Expand $s^2$

We know $s = p + q + r$, so:

$$s^2 = (p + q + r)^2 = p^2 + q^2 + r^2 + 2pq + 2qr + 2pr$$

$$s^2 = ss + 2(pq + qr + pr)$$

Rearranging to isolate $pq + qr + pr$:

$$pq + qr + pr = \frac{s^2 - ss}{2}$$

## Step 2: Expand $\phi(N)$

For $N = pqr$:

$$\phi(N) = (p - 1)(q - 1)(r - 1)$$

$$= pqr - pq - qr - pr + p + q + r - 1$$

$$= N - (pq + qr + pr) + s - 1$$

Substituting $pq + qr + pr$ from Step 1:

$$\phi(N) = N - \frac{s^2 - ss}{2} + s - 1$$

Multiplying both sides by $2$:

$$2\phi(N) = 2N - s^2 + ss + 2s - 2$$

Rearranging to bring $s^2$ terms to the LHS:

$$s^2 - ss - 2s = 2N - 2\phi(N) - 2$$

## Step 3: Work with the Exponents

From the ciphertexts, we can construct a new value by combining exponents:

$$ct_3 \cdot ct_4^{-1} \cdot ct_2^{-2} = m^{s^2} \cdot m^{-ss} \cdot m^{-2s} = m^{s^2 - ss - 2s} \bmod N$$

Substituting from Step 2:

$$ct_5 = m^{2N - 2\phi(N) - 2} \bmod N$$

By Euler's theorem, $m^{\phi(N)} \equiv 1 \pmod{N}$, so the $-2\phi(N)$ in the exponent vanishes:

$$ct_5 \equiv m^{2N - 2} \pmod{N}$$

## Step 4: Set Up the System of Exponents

We now have two equations with known bases and exponents in $m$:

$$ct_5 \equiv m^{2N - 2} \pmod{N}$$

$$ct_1 \equiv m^{e} \pmod{N}$$

We want to find $m = m^1$, so we need integers $a, b$ satisfying:

$$a \cdot (2N - 2) + b \cdot e = 1$$

Since $e = 65537$ is an odd prime and $2N - 2$ is even, $\gcd(2N - 2, e) = 1$, so such $a, b$ exist by Bezout's identity and can be found via the extended Euclidean algorithm.

## Step 5: Recover $m$

Once we have $a$ and $b$:

$$ct_5^{\,a} \cdot ct_1^{\,b} \equiv m^{a(2N-2)} \cdot m^{be} \equiv m^{a(2N-2) + be} \equiv m^1 \equiv m \pmod{N}$$

Full Script: [solve.py](solv.py)