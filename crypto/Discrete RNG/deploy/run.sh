#!/bin/sh
cd /app

# 2. Run python with unbuffered output (-u)
# 3. Redirect stderr to stdout (2>&1) so you can see crash messages on netcat
exec /usr/local/bin/python -u chal.py 2>&1
