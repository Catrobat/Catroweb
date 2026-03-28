FROM python:3.12-slim

WORKDIR /app

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY app.py .

# Download model at build time
RUN python -c "from transformers import pipeline; pipeline('image-classification', model='Falconsai/nsfw_image_detection')"

EXPOSE 5000

CMD ["python", "app.py"]
