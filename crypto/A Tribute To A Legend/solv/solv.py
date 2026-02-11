from Crypto.Util.number import long_to_bytes
from pwn import remote

p = remote("localhost", 6100)

def extended_gcd(a, b):
    if a == 0:
        return b, 0, 1
    gcd, x1, y1 = extended_gcd(b % a, a)
    x = y1 - (b // a) * x1
    y = x1
    return gcd, x, y

N = int(p.recvline().decode().strip().split(" = ")[1])
ct1 = int(p.recvline().decode().strip().split(" = ")[1])
ct2 = int(p.recvline().decode().strip().split(" = ")[1])
ct3 = int(p.recvline().decode().strip().split(" = ")[1])
ct4 = int(p.recvline().decode().strip().split(" = ")[1])
e = 65537

ct5 = (ct3 * pow(ct4, -1, N) * pow(ct2, -2, N)) % N

gcd, a, b = extended_gcd(2*N - 2, e)

guess = long_to_bytes((pow(ct5, a, N) * pow(ct1, b, N)) % N)

p.sendlineafter(b"Enter the secret number: ", str(int.from_bytes(guess, 'big')).encode())

print(p.recvline().decode().strip())
