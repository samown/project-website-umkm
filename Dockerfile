# Dockerfile for Toko Rini
FROM python:3.11-slim

ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1

WORKDIR /app

# Install build dependencies required by bcrypt
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    build-essential \
    libssl-dev \
    libffi-dev \
    gcc \
    && rm -rf /var/lib/apt/lists/*

# Copy requirements first to leverage Docker cache
COPY requirements.txt /app/requirements.txt

RUN pip install --upgrade pip \
    && pip install --no-cache-dir -r /app/requirements.txt

# Copy project files
COPY . /app

# Ensure upload folder exists
RUN mkdir -p /app/app/static/uploads

EXPOSE 5000

CMD ["python", "run.py"]
