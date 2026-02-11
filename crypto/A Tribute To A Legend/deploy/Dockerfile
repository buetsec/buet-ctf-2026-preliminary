# Use standard python image
FROM python:slim

# Install socat (handles the network connection)
RUN apt-get update && apt-get install -y socat

# Set working directory
WORKDIR /app

# Install dependencies
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copy challenge files
COPY flag.txt .
COPY chall.py .

# Expose the port
EXPOSE 5000

# Run socat.
# It listens on 5000 and runs "python -u chall.py" for every new connection.
CMD socat TCP-LISTEN:5000,fork,reuseaddr EXEC:"python -u chall.py",stderr
