from Crypto.Util.number import bytes_to_long, getPrime
import os

flag = open("flag.txt", "r").read().strip()

secret = os.urandom(256)
m = bytes_to_long(secret)

prime_bits = 1024
p = getPrime(prime_bits)
q = getPrime(prime_bits)

n = p * q

mask_bits = 532
x = p ^ q

x = x % (1 << mask_bits)
e = 65537
c = pow(m, e, n)

print(f"{n = }")
print(f"{x = }")
print(f"{c = }")

guess = int(input("Enter the secret number: "))

if guess == m:
    print(flag)
else:
    print("X")
