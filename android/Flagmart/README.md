### Challenge Name: `Flagmart`
### Challenge Author: `Maruf Bin Murtuza` [(marufmurtuza)](https://x.com/marufmurtuza)
### Challenge Difficulty: `MEDIUM`
### Challenge Flag: 
```
buetctf{BCF_26_Flagmart_Sp3cial_Fl4g_f0r_5pecially_5kill3d_hackerz}
```

### Challenge Artifact: [Flagmart.apk](Flagmart.apk)

### Challenge Description:
```
A digital marketplace for flags!
```

### Challenge Solution:

- Signup as a new user.
- Login to the user account.
- During login, the `Authorization` header tempering would reveal a special promo code.
- Using the promo code will unlock a special flag.
- Tamper the balance transfer section (send negative values to the server using the interceptor) to obtain more balances.
- Buy the special flag.
- Decode the special flag using base64 decoder.

# Special Note:
```
The required API for this challenge is currently hosted on my server. However, for stability and scalability during the event, deployment on the event infrastructure is recommended. If you prefer to self‑host, please share a Docker container so the challenge can be deployed within the event environment.
```