### Challenge Name: `Wannalaugh`
### Challenge Author: `Maruf Bin Murtuza` [(marufmurtuza)](https://x.com/marufmurtuza)
### Challenge Category: `Malware + Reverse + Network`
### Challenge Difficulty: `HARD`
### Challenge Flag: 
```
buetctf{wannalaugh_50unds_lik3_the_0ppo5it3_0f_wannacry_so_as_its_50lution}
```

### Challenge Artifact: [Wannalaugh.exe](Wannalaugh.exe)

### Challenge Description:
```
Wannalaugh sounds familiar, but it doesn’t play by familiar rules.
What you expect to trigger may actually prevent the outcome.
Pay attention to absence as much as presence.
Sometimes, things work best when they don’t exist.
```

### Challenge Solution:

- Run `Wireshark` as Administrator and use the filter `dns`.
- Run `Wannalaugh.exe` and check the DNS Query in `Wireshark` to find the domain.
- The killswitch logic for `Wannalaugh.exe` is it requires the domain to be unregistered.
- As `Wannalaugh.exe` uses a valid domain, the DNS Query needs to be spoofed which can be done in various ways. But for simplicity, a GUI app called [Acrylic DNS Proxy](https://mayakron.altervista.org/support/acrylic/Home.htm) can be used.
- Install `Acrylic DNS Proxy` and add the following rules in Acrylic DNS Proxy Host File:
```
NX evil.com
NX *.evil.com
```
- Navigate to Control Panel >  Network and Sharing Center > Change adapter setting > Properties of the valid network adapter > Properties of Internet Protocol Version 4 (TCP/IPv4).
- Set the preferred DNS server to `127.0.0.1`. Alternate DNS server is preffered to left blank otherwise set to `8.8.8.8` or `1.1.1.1`.
- Then Reload the modified `Acrylic` hostfile and restart `Acrylic DNS Proxy` service. It will activate the killswitch for the Malware.
- Then Run `Wannalaugh.exe` to get the flag.


