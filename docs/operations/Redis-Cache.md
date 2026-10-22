# Redis Cache

Production uses Redis for the Symfony `cache.app` pool and Doctrine caches (`config/packages/prod/`, `REDIS_URL`). Dev and test use the filesystem cache, so no Redis is needed locally.

## Server setup

Redis runs on the application server, localhost only.

```bash
apt-get install -y redis-server php-redis
```

`/etc/redis/redis.conf`:

```ini
bind 127.0.0.1 -::1
maxmemory 256mb            # adjust to server RAM
maxmemory-policy allkeys-lru
```

Verify with `redis-cli ping` (→ `PONG`) and `redis-cli INFO memory`.

## Troubleshooting

| Symptom                              | Fix                                                       |
| ------------------------------------ | --------------------------------------------------------- |
| `RedisException: Connection refused` | `systemctl start redis-server`                            |
| Stale data after deploy              | `bin/console cache:pool:clear cache.app`                  |
| PHP error: class Redis not found     | `apt-get install php-redis && systemctl restart php*-fpm` |
| High memory usage                    | Lower `maxmemory` or reduce TTLs                          |
