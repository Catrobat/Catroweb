FROM python:3.12-slim

ENV HF_HOME=/home/app/.cache/huggingface
ENV PYTHONDONTWRITEBYTECODE=1
ENV PIP_NO_CACHE_DIR=1

RUN useradd --create-home --shell /usr/sbin/nologin app

WORKDIR /app

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY app.py .

# Download model at build time into the non-root user's HF cache
RUN mkdir -p "$HF_HOME" \
  && python -c "from transformers import pipeline; pipeline('image-classification', model='Falconsai/nsfw_image_detection')" \
  && chown -R app:app /home/app

USER app

EXPOSE 5000

CMD ["gunicorn", "-w", "1", "-b", "0.0.0.0:5000", "--timeout", "30", "app:app"]
