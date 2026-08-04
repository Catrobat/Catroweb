FROM python:3.12-slim

ENV HF_HOME=/home/app/.cache/huggingface
ENV PYTHONDONTWRITEBYTECODE=1
ENV PIP_NO_CACHE_DIR=1

RUN useradd --create-home --shell /usr/sbin/nologin app

WORKDIR /app

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY app.py .

# Download model at build time into the non-root user's HF cache.
# Anonymous huggingface.co requests get rate limited (HTTP 429) on shared CI runners,
# which fails the build for reasons that have nothing to do with the change under test.
# Retry with a growing backoff; the final call verifies the model really is cached.
RUN mkdir -p "$HF_HOME" \
  && for attempt in 1 2 3 4 5; do \
       python -c "from transformers import pipeline; pipeline('image-classification', model='Falconsai/nsfw_image_detection')" && break; \
       echo "Model download failed (attempt $attempt/5), retrying in $((attempt * 20))s"; \
       sleep "$((attempt * 20))"; \
     done \
  && python -c "from transformers import pipeline; pipeline('image-classification', model='Falconsai/nsfw_image_detection')" \
  && chown -R app:app /home/app

USER app

EXPOSE 5000

CMD ["gunicorn", "-w", "1", "-b", "0.0.0.0:5000", "--timeout", "30", "app:app"]
