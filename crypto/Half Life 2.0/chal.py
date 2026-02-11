from Crypto.Util.number import bytes_to_long
from flag import flag

sz = len(flag)
le, ri = bytes_to_long(flag[:sz//2].encode()), bytes_to_long(flag[sz//2:].encode())

p = getPrime(256)
a, b = [getPrime(128) for _ in 'o0']
assert bytes_to_long(flag.encode()) < p

hint = (a*le + b*ri) % p
print(p, a, b, hint)
#106647666884337899272703843104883505571051554221013158136943062524333273209153 262479512657676921300479624666431888987 212336732202255918295082781769645914517 61287736693159545217447997957330889930912497156388163976820756549417048028749
