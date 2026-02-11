#!/usr/local/bin/python
from Crypto.Util.number import *
from random import randint
from fastcrc import crc16

flag = open('flag.txt', 'rb').read()

def xor(a, b):
    return bytes([ai^bi for ai, bi in zip(a, b)])

def evaluate_poly(coeffs, x, p):
    res = 0
    for i, c in enumerate(coeffs):
        res += pow(x, i, p) * c
        res %= p
    return res

class RNG:
    def __init__(self, coeffs, a, c, p):
        self.coeffs = coeffs
        self.a, self.c, self.p = a, c, p
        self.seed, self.g = randint(0, self.p), randint(0, self.p)
        self.itr = 0
        self.bounds = (self.c & 127) | 64
        
    def go(self):
        ret = pow(self.g, self.seed, self.p)
        multiplier = crc16.xmodem(long_to_bytes(evaluate_poly(self.coeffs, self.itr, self.p)))
        if multiplier < (self.bounds // 2) - 10 or multiplier > self.bounds + 10:
            multiplier = randint(1, self.p)
        self.seed = (self.a*self.seed + multiplier * self.c) % (self.p - 1)
        self.itr += 1
        return long_to_bytes(ret)

print('(╯_╰)')

p = getPrime(512)
print(p)

a = randint(2, p - 1)
print(a)

c = randint(2, p - 1)
print(c & 127)

coeffs = list(map(int, input('Enter comma separated polynomial coefficients: ').split(',')))
assert 50 < len(set(coeffs)) < 200, '╭∩╮（︶︿︶）╭∩╮'
for ci in coeffs:
    assert 1 < ci < p - 1, '┌∩┐(⋟﹏⋞)┌∩┐'

rng = RNG(coeffs, a, c, p)
ct = xor(rng.go(), flag)
print(ct.hex())

for _ in range((c & 127) | 64):
    rng.go()

print(rng.go().hex())
