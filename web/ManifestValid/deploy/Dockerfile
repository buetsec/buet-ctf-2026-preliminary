FROM python:3.8-slim

WORKDIR /app
COPY src/ /app/
RUN pip install flask requests dnspython

EXPOSE 5000
COPY backend.py /app/backend.py
COPY flag.txt /app/flag.txt

# Run both the Flask app and the backend service
CMD ["sh", "-c", "python backend.py & python app.py"]